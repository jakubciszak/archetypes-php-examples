# E-Commerce Loyalty Program - Entry-Based Ledger Implementation

This is a **complete implementation of the Accounting archetype** from "Software Archetypes" by David Hay and Heidi J.S. Fougner (Chapter 7: Using the Accounting Models), specifically designed for **LPP company's multi-market e-commerce loyalty program**.

## Architecture Overview

This implementation follows the **pure accounting archetype pattern** with:

1. **Immutable Entry-Based Ledger** - All changes are recorded as entries, never mutated
2. **Hierarchical Sub-Accounts** - Different point states tracked separately
3. **Transaction → PostingRule → Entry** - Separation of business logic and accounting mechanics
4. **AccountingPractice** - Market-specific rules bundled for easy configuration
5. **Line-Level Allocation** - Deterministic partial returns
6. **Balance Calculation from Entries** - No stored balances, always calculated

## Key Differences from Simplified Version

| Aspect | Simplified (Previous) | Entry-Based Ledger (This) |
|--------|----------------------|---------------------------|
| **State Management** | Mutable `activePoints`, `pendingPoints` | Immutable entries, calculated balances |
| **Account Structure** | Flat (2 fields) | Hierarchical (7 sub-accounts) |
| **Transactions** | Direct method calls | Domain events interpreted by PostingRules |
| **Reversals** | Delete/modify objects | Contra-entries (negative amounts) |
| **Audit Trail** | Events only | Complete ledger with all entries |
| **Partial Returns** | Not supported | Line-level allocation |
| **Flexibility** | Hardcoded logic | PostingRules can be changed/added |

## Domain Model

### 1. Hierarchical Account Structure

```
LoyaltyAccount (root)
├── PendingFromPurchases     - Points earned from purchases, awaiting activation
├── PendingFromPromos        - Points from promotions, awaiting activation
├── ActivePoints             - Points available for redemption
├── SpentPoints              - Points that have been redeemed
├── ExpiredPoints            - Points that expired before use
├── ReversedPoints           - Points reversed due to returns
└── AdjustmentPoints         - Manual adjustments (corrections, compensations)
```

**Balance Calculation:**
```php
$activeBalance = $account->balance(AccountType::ACTIVE_POINTS);
// Internally: sum of all entries in ActivePoints sub-account
```

### 2. Entry - The Fundamental Building Block

```php
$entry = Entry::create(
    EntryId::generate(),
    AccountType::PENDING_FROM_PURCHASES,  // Which account
    Points::of(1000),                     // Amount (can be negative!)
    new DateTimeImmutable('2024-01-01'),  // When
    'TXN-001',                            // Transaction that caused it
    'Purchase PURCH-001 - Line 1',       // Description
    'PURCH-001',                          // Reference (purchase_id)
    'LINE-001',                           // Line item (for partial returns)
    ['market_id' => 'PL', ...]           // Metadata
);
```

**Properties:**
- **Immutable** - Once created, never changes
- **Can be negative** - For reversals, deductions (accounting standard)
- **Tracks line items** - Enables deterministic partial returns
- **Rich metadata** - Maturation dates, conversion rates, product IDs

### 3. Transaction - Business Events

Transactions are **business facts**, NOT accounting entries:

```php
// Business event: Customer completed a purchase
$transaction = new PurchaseCompleted(
    'TXN-001',
    'PURCHASE-001',
    'CUST-001',
    Money::of(10000), // 100 PLN
    [
        'item1' => [
            'lineItemId' => 'LINE-001',
            'amount' => Money::of(5000),
            'productId' => 'SHIRT-001',
        ],
    ],
    MarketId::fromString('PL'),
    new DateTimeImmutable('2024-01-01')
);
```

**Available Transactions:**
- `PurchaseCompleted` - Customer made a purchase
- `ReturnAccepted` - Customer returned product(s)
- `PromotionAwarded` - Bonus points from promotions
- `MaturationPeriodExpired` - Pending points become active
- `PointsRedeemed` - Customer spent points
- `PointsExpired` - Points expired before use

### 4. PostingRule - The KEY Pattern

PostingRules **interpret transactions** and create appropriate ledger entries.

**The Pattern from Chapter 7:**
```
Business Event (Transaction)
    ↓
PostingRule examines transaction
    ↓
Creates Entry(ies) in appropriate Account(s)
    ↓
Balance automatically updated (sum of entries)
```

**Example - PurchaseCompletedPostingRule:**
```php
public function process(Transaction $transaction, LoyaltyAccount $account): void
{
    $practice = $account->accountingPractice();

    foreach ($transaction->lineItems() as $lineItem) {
        // Calculate points based on AccountingPractice
        $points = $practice->calculatePoints($lineItem['amount'], $lineItem['productId']);

        // Calculate maturation date
        $maturationDate = $transaction->occurredAt()->modify(
            sprintf('+%d days', $practice->maturationPeriodDays())
        );

        // Create entry in PendingFromPurchases
        $entry = Entry::create(
            EntryId::generate(),
            AccountType::PENDING_FROM_PURCHASES,
            $points,
            $transaction->occurredAt(),
            $transaction->transactionId(),
            sprintf('Purchase %s - Line %s', $purchaseId, $lineItemId),
            $purchaseId,
            $lineItemId,
            ['maturation_date' => $maturationDate, ...]
        );

        $account->addEntry($entry);
    }
}
```

**Benefits:**
- Business logic (PurchaseCompleted) separated from accounting mechanics (entries)
- Rules can be changed without touching domain events
- Easy to add new rules for new transaction types
- Testable in isolation

### 5. AccountingPractice - Market-Specific Rules Bundle

```php
$practicePL = AccountingPractice::forMarket(
    MarketId::fromString('PL'),
    'Poland',
    pointsPerCurrencyUnit: 10,       // 10 points per 1 PLN
    maturationPeriodDays: 14,        // 14 days return period
    pointsExpirationDays: 365,       // Points expire after 1 year
    roundDown: true,
    promotionalMultipliers: [
        'JACKET-001' => 2.0,         // 2x points for premium jackets
    ]
);

$practiceDE = AccountingPractice::forMarket(
    MarketId::fromString('DE'),
    'Germany',
    pointsPerCurrencyUnit: 15,       // 15 points per 1 EUR
    maturationPeriodDays: 30,        // 30 days return period (German law)
    pointsExpirationDays: 730,       // 2 years
    roundDown: false,                // Round to nearest
);
```

**Encapsulates:**
- Conversion ratios
- Pending maturation periods (return windows)
- Expiration rules
- Rounding behavior
- Product-specific bonuses

## Complete Usage Example

```php
// 1. Setup
$practicePL = AccountingPractice::forMarket(
    MarketId::fromString('PL'),
    'Poland',
    10, // 10 points per PLN
    14  // 14 days maturation
);

// 2. Create account
$account = LoyaltyAccount::create(
    LoyaltyAccountId::generate(),
    'CUST-001',
    'Jan Kowalski',
    $practicePL
);

// 3. Register posting rules
$account->registerPostingRule(new PurchaseCompletedPostingRule());
$account->registerPostingRule(new ReturnAcceptedPostingRule());
$account->registerPostingRule(new PromotionAwardedPostingRule());
$account->registerPostingRule(new MaturationPeriodExpiredPostingRule());
$account->registerPostingRule(new PointsRedeemedPostingRule());

// 4. Process business events

// Customer makes purchase
$purchase = new PurchaseCompleted(
    'TXN-001',
    'PURCHASE-001',
    'CUST-001',
    Money::of(10000), // 100 PLN = 1000 points
    [
        'item1' => [
            'lineItemId' => 'LINE-001',
            'amount' => Money::of(5000),
            'productId' => 'SHIRT-001',
        ],
        'item2' => [
            'lineItemId' => 'LINE-002',
            'amount' => Money::of(5000),
            'productId' => 'JACKET-001', // Has 2x multiplier!
        ],
    ],
    MarketId::fromString('PL'),
    new DateTimeImmutable('2024-01-01')
);

$account->processTransaction($purchase);
// Points: Active: 0, Pending: 1500 (500 + 1000 with bonus)

// Award promotion (immediate activation)
$promo = new PromotionAwarded(
    'TXN-002',
    'CUST-001',
    'PROMO-CHECKIN',
    'check-in-streak',
    Points::of(100),
    true, // immediateActivation
    null,
    new DateTimeImmutable('2024-01-02')
);

$account->processTransaction($promo);
// Points: Active: 100, Pending: 1500

// Customer returns one item
$return = new ReturnAccepted(
    'TXN-003',
    'PURCHASE-001',
    'CUST-001',
    ['LINE-002'], // Return jacket
    new DateTimeImmutable('2024-01-05')
);

$account->processTransaction($return);
// Points: Active: 100, Pending: 500, Reversed: 1000

// Maturation period expires - activate pending
$maturation = new MaturationPeriodExpired(
    'TXN-004',
    'CUST-001',
    AccountType::PENDING_FROM_PURCHASES,
    $pendingEntryIds,
    new DateTimeImmutable('2024-01-16')
);

$account->processTransaction($maturation);
// Points: Active: 600, Pending: 0

// Redeem points
$redemption = new PointsRedeemed(
    'TXN-005',
    'CUST-001',
    Points::of(200),
    'REDEMPTION-001',
    'voucher',
    new DateTimeImmutable('2024-01-20')
);

$account->processTransaction($redemption);
// Points: Active: 400, Spent: 200

// Check balances
echo $account->activePoints()->amount();     // 400
echo $account->spentPoints()->amount();      // 200
echo $account->reversedPoints()->amount();   // 1000
```

## Key Business Scenarios

### Scenario 1: Purchase with Line-Level Tracking

```php
$purchase = new PurchaseCompleted(
    'TXN-001',
    'PURCH-001',
    'CUST-001',
    Money::of(15000),
    [
        'line1' => ['lineItemId' => 'L1', 'amount' => Money::of(5000), 'productId' => 'P1'],
        'line2' => ['lineItemId' => 'L2', 'amount' => Money::of(5000), 'productId' => 'P2'],
        'line3' => ['lineItemId' => 'L3', 'amount' => Money::of(5000), 'productId' => 'P3'],
    ],
    MarketId::fromString('PL'),
    new DateTimeImmutable()
);

$account->processTransaction($purchase);

// Creates 3 separate entries in PendingFromPurchases:
// Entry 1: LINE-L1, 500 points
// Entry 2: LINE-L2, 500 points
// Entry 3: LINE-L3, 500 points
```

### Scenario 2: Partial Return with Deterministic Reversal

```php
// Customer returns only line 2
$return = new ReturnAccepted(
    'TXN-002',
    'PURCH-001',
    'CUST-001',
    ['L2'], // Only this line item
    new DateTimeImmutable()
);

$account->processTransaction($return);

// Creates entries:
// In PendingFromPurchases: -500 points (LINE-L2)
// In ReversedPoints: +500 points (LINE-L2)

// Lines L1 and L3 remain intact and will activate normally
```

### Scenario 3: Return After Activation

```php
// Points already activated
$account->balance(AccountType::ACTIVE_POINTS); // 1500

// Customer returns item
$return = new ReturnAccepted(
    'TXN-003',
    'PURCH-001',
    'CUST-001',
    ['L1'],
    new DateTimeImmutable()
);

$account->processTransaction($return);

// Creates entries:
// In ActivePoints: -500 points (deducted)
// In ReversedPoints: +500 points (tracking)

$account->balance(AccountType::ACTIVE_POINTS); // 1000
```

### Scenario 4: Multi-Market Operations

```php
// Purchase in Poland
$accountPL = LoyaltyAccount::create($id, $customerId, $name, $practicePL);
// ... register rules ...

$purchasePL = new PurchaseCompleted(/*... Market PL ...*/);
$accountPL->processTransaction($purchasePL);
// 100 PLN = 1000 points, 14 days maturation

// Purchase in Germany (same customer, different account or practice)
$accountDE = LoyaltyAccount::create($id2, $customerId, $name, $practiceDE);
// ... register rules ...

$purchaseDE = new PurchaseCompleted(/*... Market DE ...*/);
$accountDE->processTransaction($purchaseDE);
// 100 EUR = 1500 points, 30 days maturation
```

### Scenario 5: Promotional Campaigns

```php
// Check-in bonus (immediate)
$checkIn = new PromotionAwarded(
    'TXN-PROMO-1',
    'CUST-001',
    'CHECKIN-7DAYS',
    'check-in-streak',
    Points::of(100),
    true, // immediate activation
    null,
    new DateTimeImmutable()
);

$account->processTransaction($checkIn);
// Immediately added to ActivePoints

// Product bonus (pending, tied to purchase)
$productBonus = new PromotionAwarded(
    'TXN-PROMO-2',
    'CUST-001',
    'JACKET-BONUS',
    'product-bonus',
    Points::of(50),
    false, // not immediate - follows same maturation as purchase
    'PURCHASE-001',
    new DateTimeImmutable()
);

$account->processTransaction($productBonus);
// Added to PendingFromPromos, will activate after maturation period
```

## Ledger Visualization

After processing multiple transactions, the ledger looks like this:

```
Pending from Purchases: 500 points
  +500  | Purchase PURCH-001 - Line L1 (500 points pending until 2024-01-15)
  +1000 | Purchase PURCH-001 - Line L2 (1000 points pending until 2024-01-15)
  -1000 | Return - Purchase PURCH-001 Line L2 (reversed 1000 pending points)

Active Points: 600 points
  +100  | Promotion CHECKIN-7DAYS - check-in-streak (100 points - immediate)
  +500  | Activated - 500 points from Pending from Purchases (ref: PURCH-001)

Spent Points: 200 points
  +200  | Spent - voucher (200 points)

Reversed Points: 1000 points
  +1000 | Return - Purchase PURCH-001 Line L2 (1000 points reversed from pending)

Balance Calculation:
- Active = 100 + 500 = 600 ✓
- Pending = 500 + 1000 - 1000 = 500 ✓
- Reversed = 1000 ✓
```

## Benefits of Entry-Based Ledger Approach

### 1. **Complete Audit Trail**
Every change is recorded as an entry. You can see:
- When points were earned
- Why they were earned
- When they were activated
- If they were reversed
- Who redeemed them

### 2. **Deterministic State**
Balance is ALWAYS calculated from entries:
```php
// No stored balance field!
public function balance(): Points {
    return array_reduce(
        $this->entries,
        fn($total, $entry) => $total->add($entry->amount()),
        Points::zero()
    );
}
```

### 3. **Flexible Business Rules**
Want to change how purchases are processed? Just modify or replace the PostingRule:
```php
// Old rule: 10 points per PLN
class OldPurchaseRule implements PostingRule { ... }

// New rule: 15 points per PLN, but only after verification
class NewPurchaseRule implements PostingRule { ... }

// Switch at runtime!
$account->registerPostingRule(new NewPurchaseRule());
```

### 4. **Event Sourcing Ready**
The ledger IS essentially an event log:
- Entry = Event
- Balance = Projection
- Can rebuild state from entries
- Can replay to any point in time

### 5. **Regulatory Compliance**
Financial systems require immutable audit trails. This provides:
- Full transaction history
- Cannot be modified or deleted
- Reversals via contra-entries (standard accounting practice)
- Deterministic balance calculation

### 6. **Support for Complex Scenarios**
- **Partial returns** - Line-level allocation
- **Multi-currency** - Different practices per market
- **Expiration** - Track expired vs. active
- **Adjustments** - Dedicated account type
- **Reporting** - Query entries by date, type, reference

## Architecture Patterns Applied

### From "Software Archetypes" Chapter 7

1. **Entry** (p. 133) - "The fundamental building block of any accounting system"
   - ✅ Immutable entries with amount, date, description
   - ✅ Reference to transaction that created it
   - ✅ Can be negative (contra-entries)

2. **Account** (p. 135) - "A collection of entries with a calculated balance"
   - ✅ Hierarchical structure
   - ✅ Balance = sum of entries
   - ✅ Never stores balance

3. **Transaction** (p. 137) - "Business events that cause entries"
   - ✅ Separate from entries
   - ✅ Interpreted by PostingRules
   - ✅ Domain events (PurchaseCompleted, not CreateEntry)

4. **PostingRule** (p. 139) - "The KEY pattern that interprets transactions"
   - ✅ Examines transaction data
   - ✅ Creates appropriate entries
   - ✅ Encapsulates business logic
   - ✅ Can be changed/added without touching transactions

5. **AccountingPractice** (Extension) - Bundle of rules per context
   - ✅ Market-specific configurations
   - ✅ Conversion rates, maturation periods
   - ✅ Promotional multipliers

## Testing

Run the demo:
```bash
php src/Loyalty/example-usage.php
```

This demonstrates:
- Purchase with line-level tracking
- Promotional bonuses
- Partial returns (before and after activation)
- Points maturation
- Points redemption
- Complete ledger visualization

## File Structure

```
accounting/src/Loyalty/
├── Domain/
│   ├── Entry.php                          # Immutable ledger entry
│   ├── EntryId.php
│   ├── Account.php                        # Sub-account with entries
│   ├── AccountType.php                    # Enum for sub-account types
│   ├── LoyaltyAccount.php                 # Aggregate root
│   ├── LoyaltyAccountId.php
│   ├── Points.php                         # Value object (can be negative!)
│   ├── MarketId.php
│   ├── AccountingPractice.php             # Market-specific rules bundle
│   │
│   ├── Transactions/                      # Domain events
│   │   ├── PurchaseCompleted.php
│   │   ├── ReturnAccepted.php
│   │   ├── PromotionAwarded.php
│   │   ├── MaturationPeriodExpired.php
│   │   ├── PointsRedeemed.php
│   │   └── PointsExpired.php
│   │
│   └── PostingRules/                      # Business logic interpreters
│       ├── PostingRule.php                # Interface
│       ├── PurchaseCompletedPostingRule.php
│       ├── ReturnAcceptedPostingRule.php
│       ├── PromotionAwardedPostingRule.php
│       ├── MaturationPeriodExpiredPostingRule.php
│       ├── PointsRedeemedPostingRule.php
│       └── PointsExpiredPostingRule.php
│
├── Infrastructure/
│   └── InMemoryLoyaltyAccountRepository.php
│
└── example-usage.php                      # Complete demonstration
```

## Migration from Simplified Version

If you have the simplified version, key changes:

1. **LoyaltyAccount structure**
   - Old: `activePoints`, `pendingPoints` fields
   - New: Hierarchical sub-accounts with entries

2. **State changes**
   - Old: Direct mutation (`$this->activePoints = ...`)
   - New: Add entries (`$account->addEntry(...)`)

3. **Operations**
   - Old: Method calls (`$account->recordPurchase(...)`)
   - New: Process transactions (`$account->processTransaction(new PurchaseCompleted(...))`)

4. **Balance queries**
   - Old: Direct field access (`$account->activePoints()`)
   - New: Calculated from entries (`$account->balance(AccountType::ACTIVE_POINTS)`)

## References

- **Book**: "Software Archetypes" by David Hay and Heidi J.S. Fougner
- **Chapter 7**: Using the Accounting Models (pages 133-145)
- **Key Patterns**:
  - Entry (p. 133)
  - Account (p. 135)
  - Transaction (p. 137)
  - PostingRule (p. 139)
- **Figure 7.2**: Accounts model for IT (p. 135)

## License

MIT License
