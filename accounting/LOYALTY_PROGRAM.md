# E-Commerce Loyalty Program - Accounting Model

This is an extended implementation of the **Accounting archetype** specifically designed for e-commerce loyalty programs, based on patterns from "Software Archetypes" by David Hay and Heidi J.S. Fougner (Chapter 7: Using the Accounting Models).

## Overview

This loyalty program implementation demonstrates how the Accounting archetype can be applied to real-world e-commerce scenarios, specifically modeling the **LPP company's multi-market loyalty program**. The system handles:

- **Multi-market operations** with different conversion rates (PLN, EUR, etc.)
- **Pending points** that activate after return period expires
- **Product returns** with automatic points reversal
- **Promotional campaigns** (check-ins, product bonuses, quick pickup rewards)
- **Event-driven architecture** for integration with other systems

## Domain Model

### Core Concepts from Accounting Archetype

Following the book's guidance (Figure 7.2 - Accounts model), our loyalty program implements:

**PostingRule** - Defines market-specific conversion rates and business rules:
```
Market PL: 1 PLN (100 groszy) → 10 points, 14 days return period
Market DE: 1 EUR (100 cents) → 15 points, 30 days return period
```

**LoyaltyAccount** - The aggregate root (similar to Account in the archetype):
- Active points (ready to use)
- Pending points (awaiting activation)
- Transaction history
- Event publishing

**Entry Types**:
- Purchase entries (pending points)
- Promotional entries (immediate points)
- Activation entries (pending → active)
- Reversal entries (product returns)

### Key Entities and Value Objects

#### 1. Points
Represents loyalty points (similar to Money in the base archetype):
```php
$points = Points::of(1000);
$total = $points->add(Points::of(500)); // 1500 points
```

#### 2. PostingRule
Converts purchase amounts to points based on market:
```php
$rule = PostingRule::create(
    MarketId::fromString('PL'),
    'Poland',
    pointsPerCurrencyUnit: 10,
    returnPeriodDays: 14
);

$points = $rule->calculatePoints(Money::of(10000)); // 1000 points
```

#### 3. PendingPoints
Tracks points awaiting activation:
```php
$pending = PendingPoints::forPurchase(
    $purchaseId,
    Points::of(1000),
    purchaseDate: new DateTimeImmutable('2024-01-01'),
    activationDate: new DateTimeImmutable('2024-01-15')
);

// Can activate after return period
if ($pending->canActivate($currentDate)) {
    $pending->activate();
}
```

#### 4. Promotional Actions
Various ways to earn bonus points:

**Check-in Series**:
```php
$action = CheckInSeriesAction::create(
    'checkin-7days',
    consecutiveDays: 7,
    bonusPoints: Points::of(100),
    validFrom: new DateTimeImmutable('2024-01-01'),
    validTo: new DateTimeImmutable('2024-12-31')
);
```

**Product Bonus**:
```php
$action = ProductBonusAction::create(
    'winter-sale',
    productId: 'JACKET-001',
    productName: 'Premium Winter Jacket',
    bonusPoints: Points::of(50),
    validFrom: new DateTimeImmutable('2024-01-01'),
    validTo: new DateTimeImmutable('2024-01-31')
);
```

**Quick Pickup**:
```php
$action = QuickPickupAction::create(
    'quick-pickup-24h',
    maxHoursForBonus: 24,
    bonusPoints: Points::of(30),
    validFrom: new DateTimeImmutable('2024-01-01'),
    validTo: new DateTimeImmutable('2024-12-31')
);
```

## Business Scenarios

### Scenario 1: Standard Purchase Flow

```php
// 1. Create customer account
$accountId = LoyaltyAccountId::generate();
$facade->createAccount($accountId, 'CUST-001', 'Jan Kowalski');

// 2. Record purchase in Poland
$purchaseId = PurchaseId::generate();
$rulePL = PostingRule::create(
    MarketId::fromString('PL'),
    'Poland',
    10, // 10 points per PLN
    14  // 14 days return period
);

$facade->recordPurchase(
    $accountId,
    $purchaseId,
    Money::of(25000), // 250 PLN
    $rulePL,
    new DateTimeImmutable('2024-01-01')
);

// Points are pending (not yet active)
// Active: 0, Pending: 2500

// 3. After 14 days, activate pending points
$facade->activatePendingPoints(
    $accountId,
    new DateTimeImmutable('2024-01-15')
);

// Active: 2500, Pending: 0
```

### Scenario 2: Product Return Before Activation

```php
// Record purchase
$purchaseId = PurchaseId::generate();
$facade->recordPurchase(
    $accountId,
    $purchaseId,
    Money::of(10000), // 100 PLN = 1000 points
    $rulePL,
    new DateTimeImmutable('2024-01-01')
);

// Customer returns product within return period
$facade->reversePurchase(
    $accountId,
    $purchaseId,
    new DateTimeImmutable('2024-01-05')
);

// Points reversed before activation
// Active: 0, Pending: 0
```

### Scenario 3: Product Return After Activation

```php
// Purchase and activate
$purchaseId = PurchaseId::generate();
$facade->recordPurchase(
    $accountId,
    $purchaseId,
    Money::of(10000),
    $rulePL,
    new DateTimeImmutable('2024-01-01')
);

$facade->activatePendingPoints(
    $accountId,
    new DateTimeImmutable('2024-01-15')
);
// Active: 1000

// Customer returns product after activation
$facade->reversePurchase(
    $accountId,
    $purchaseId,
    new DateTimeImmutable('2024-01-20')
);
// Active: 0 (points deducted)
```

### Scenario 4: Multi-Market Operations

```php
// Purchase in Poland
$facade->recordPurchase(
    $accountId,
    PurchaseId::generate(),
    Money::of(10000), // 100 PLN = 1000 points
    $rulePL,
    new DateTimeImmutable('2024-01-01')
);

// Purchase in Germany
$ruleDE = PostingRule::create(
    MarketId::fromString('DE'),
    'Germany',
    15, // 15 points per EUR
    30  // 30 days return period
);

$facade->recordPurchase(
    $accountId,
    PurchaseId::generate(),
    Money::of(10000), // 100 EUR = 1500 points
    $ruleDE,
    new DateTimeImmutable('2024-01-05')
);

// After 14 days: Polish purchase activates
$facade->activatePendingPoints(
    $accountId,
    new DateTimeImmutable('2024-01-15')
);
// Active: 1000, Pending: 1500

// After 30 more days: German purchase activates
$facade->activatePendingPoints(
    $accountId,
    new DateTimeImmutable('2024-02-04')
);
// Active: 2500, Pending: 0
```

### Scenario 5: Promotional Campaigns

```php
// Award check-in bonus
$checkInAction = CheckInSeriesAction::create(
    'checkin-7days',
    7,
    Points::of(100),
    new DateTimeImmutable('2024-01-01'),
    new DateTimeImmutable('2024-12-31')
);

$facade->awardPromotionalPoints(
    $accountId,
    $checkInAction,
    new DateTimeImmutable('2024-01-10')
);
// Promotional points are immediately active
// Active: +100

// Award product bonus
$productBonus = ProductBonusAction::create(
    'premium-jacket',
    'PROD-001',
    'Winter Jacket',
    Points::of(50),
    new DateTimeImmutable('2024-01-01'),
    new DateTimeImmutable('2024-01-31')
);

$facade->awardPromotionalPoints(
    $accountId,
    $productBonus,
    new DateTimeImmutable('2024-01-15')
);
// Active: +50

// Award quick pickup bonus
$quickPickup = QuickPickupAction::create(
    'quick-pickup',
    24, // within 24 hours
    Points::of(30),
    new DateTimeImmutable('2024-01-01'),
    new DateTimeImmutable('2024-12-31')
);

$facade->awardPromotionalPoints(
    $accountId,
    $quickPickup,
    new DateTimeImmutable('2024-01-16')
);
// Active: +30
```

### Scenario 6: Complete Customer Journey

```php
use SoftwareArchetypes\Accounting\Loyalty\LoyaltyProgramFacade;

// Create account
$accountId = LoyaltyAccountId::generate();
$facade->createAccount($accountId, 'CUST-LPP-001', 'Maria Wiśniewska');

// Purchase 1: Regular shopping
$purchase1 = PurchaseId::generate();
$facade->recordPurchase(
    $accountId,
    $purchase1,
    Money::of(50000), // 500 PLN = 5000 points
    $rulePL,
    new DateTimeImmutable('2024-01-01')
);

// App check-in bonus
$facade->awardPromotionalPoints(
    $accountId,
    CheckInSeriesAction::create(
        'daily-checkin',
        1,
        Points::of(10),
        new DateTimeImmutable('2024-01-01'),
        new DateTimeImmutable('2024-12-31')
    ),
    new DateTimeImmutable('2024-01-02')
);
// Active: 10, Pending: 5000

// Purchase 2: Premium item with bonus
$purchase2 = PurchaseId::generate();
$facade->recordPurchase(
    $accountId,
    $purchase2,
    Money::of(30000), // 300 PLN = 3000 points
    $rulePL,
    new DateTimeImmutable('2024-01-05')
);

$facade->awardPromotionalPoints(
    $accountId,
    ProductBonusAction::create(
        'premium-bonus',
        'PREMIUM-JACKET',
        'Premium Jacket',
        Points::of(200),
        new DateTimeImmutable('2024-01-01'),
        new DateTimeImmutable('2024-01-31')
    ),
    new DateTimeImmutable('2024-01-05')
);
// Active: 210, Pending: 8000

// Quick package pickup
$facade->awardPromotionalPoints(
    $accountId,
    QuickPickupAction::create(
        'quick-pickup',
        24,
        Points::of(50),
        new DateTimeImmutable('2024-01-01'),
        new DateTimeImmutable('2024-12-31')
    ),
    new DateTimeImmutable('2024-01-06')
);
// Active: 260, Pending: 8000

// Activate first purchase after 14 days
$facade->activatePendingPoints(
    $accountId,
    new DateTimeImmutable('2024-01-15')
);
// Active: 5260, Pending: 3000

// Customer returns second purchase
$facade->reversePurchase(
    $accountId,
    $purchase2,
    new DateTimeImmutable('2024-01-18')
);
// Active: 5260, Pending: 0 (reversed before activation)

// Use points for reward
$facade->usePoints($accountId, Points::of(1000));
// Active: 4260
```

## Events

The system publishes domain events for all operations:

- `PointsEarned` - Purchase recorded, points pending
- `PointsActivated` - Pending points activated
- `PointsReversed` - Purchase returned, points reversed
- `PromotionalPointsAwarded` - Bonus points awarded

Events enable:
- Integration with other systems
- Audit trails
- Analytics and reporting
- Customer notifications

## Architecture

```
Loyalty/
├── Domain/
│   ├── LoyaltyAccount.php          # Aggregate root
│   ├── Points.php                  # Value object
│   ├── PostingRule.php             # Conversion rules
│   ├── PendingPoints.php           # Pending points entity
│   ├── PromotionalAction.php       # Interface
│   ├── CheckInSeriesAction.php     # Concrete action
│   ├── ProductBonusAction.php      # Concrete action
│   ├── QuickPickupAction.php       # Concrete action
│   ├── LoyaltyAccountId.php        # Identity
│   ├── PurchaseId.php              # Identity
│   ├── MarketId.php                # Identity
│   └── LoyaltyAccountRepository.php
│
├── Events/
│   ├── LoyaltyEvent.php            # Base interface
│   ├── PointsEarned.php
│   ├── PointsActivated.php
│   ├── PointsReversed.php
│   ├── PromotionalPointsAwarded.php
│   └── EventsPublisher.php
│
├── Infrastructure/
│   ├── InMemoryLoyaltyAccountRepository.php
│   └── InMemoryEventsPublisher.php
│
└── LoyaltyProgramFacade.php        # Application service
```

## Implementation based on Accounting Archetype

### Posting Rule Pattern (Chapter 7.2)

The book describes PostingRule as the key pattern for calculating entries. In our implementation:

**Traditional Accounting**:
```
PostingRule: Transfer from Account A to Account B
When: Purchase transaction
Calculate: Debit/Credit amounts
```

**Loyalty Program**:
```
PostingRule: Convert purchase amount to points
When: Purchase transaction
Calculate: Points based on market rules
Parameters:
  - Points per currency unit
  - Return period days
```

### Entry Processing

**Traditional**:
```
Entry → Account → Balance update
```

**Loyalty**:
```
Purchase → PendingPoints → Activation → Active Points
```

### Transaction Model

The book's Transaction concept (grouping related entries) maps to our domain:

- **Purchase Transaction**: Creates pending points
- **Activation Transaction**: Moves points from pending to active
- **Reversal Transaction**: Removes points (return)
- **Promotional Transaction**: Awards immediate points

## Testing

Comprehensive test suite following TDD approach:

```bash
# Run all tests
composer test

# Run specific test suites
vendor/bin/phpunit tests/Loyalty/Domain
vendor/bin/phpunit tests/Loyalty/Integration
```

Key test scenarios:
- Points calculation with different conversion rates
- Pending points activation logic
- Product return handling (before and after activation)
- Multi-market operations
- Promotional campaigns
- Complete customer journeys

## Usage Example

```php
use SoftwareArchetypes\Accounting\Loyalty\LoyaltyProgramFacade;
use SoftwareArchetypes\Accounting\Loyalty\Infrastructure\{
    InMemoryLoyaltyAccountRepository,
    InMemoryEventsPublisher
};

// Setup
$repository = new InMemoryLoyaltyAccountRepository();
$eventsPublisher = new InMemoryEventsPublisher();
$facade = new LoyaltyProgramFacade($repository, $eventsPublisher);

// Create account
$accountId = LoyaltyAccountId::generate();
$facade->createAccount($accountId, 'customer-123', 'John Doe');

// Define market rules
$rulePL = PostingRule::create(
    MarketId::fromString('PL'),
    'Poland',
    10,  // 10 points per PLN
    14   // 14 days return period
);

// Record purchase
$purchaseId = PurchaseId::generate();
$facade->recordPurchase(
    $accountId,
    $purchaseId,
    Money::of(10000), // 100 PLN
    $rulePL,
    new DateTimeImmutable()
);

// Award promotional points
$facade->awardPromotionalPoints(
    $accountId,
    CheckInSeriesAction::create(
        'checkin',
        7,
        Points::of(100),
        new DateTimeImmutable('2024-01-01'),
        new DateTimeImmutable('2024-12-31')
    ),
    new DateTimeImmutable()
);

// Activate pending points
$facade->activatePendingPoints(
    $accountId,
    new DateTimeImmutable('+15 days')
);

// Check balance
$activePoints = $facade->getActivePoints($accountId);
echo "Active points: " . $activePoints->amount(); // 1100
```

## Design Principles

This implementation follows:

1. **Domain-Driven Design** - Rich domain model with behavior
2. **Event Sourcing** - All state changes publish events
3. **Aggregate Pattern** - LoyaltyAccount as consistency boundary
4. **Value Objects** - Points, Ids are immutable
5. **Strategy Pattern** - PromotionalAction implementations
6. **Repository Pattern** - Data access abstraction
7. **Facade Pattern** - Simplified API for application layer

## References

- **Book**: "Software Archetypes" by David Hay and Heidi J.S. Fougner
- **Chapter 7**: Using the Accounting Models
- **Pattern**: Posting Rule for entry calculation
- **Application**: E-commerce loyalty programs with multi-market support

## License

MIT License
