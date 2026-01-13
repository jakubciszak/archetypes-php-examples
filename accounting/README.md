# Accounting

Double-entry bookkeeping accounting system implementation in PHP, ported from the [Software Archetypes](https://github.com/Software-Archetypes/archetypes) Java project.

## Overview

This implementation demonstrates a robust accounting system based on double-entry bookkeeping principles. It provides a foundation for building financial applications with proper domain modeling and event-driven architecture.

## Features

- **Double-Entry Bookkeeping**: Maintains balanced accounting with debit and credit entries
- **Account Management**: Support for multiple account types (Asset, Liability, Revenue, Expense, Off-Balance)
- **Transaction Processing**: Atomic transfers between accounts with automatic balance updates
- **Event-Driven Architecture**: Publishes domain events for all accounting operations
- **Strong Type Safety**: PHP 8.4 features with full type coverage
- **Clean Architecture**: Separation of concerns with Domain, Application, and Infrastructure layers

## Domain Model

### Core Concepts

**Account** - Represents a financial account with:
- Unique identifier (AccountId)
- Type (Asset, Liability, Revenue, Expense, Off-Balance)
- Name and current balance
- Collection of entries

**Entry** - Represents a single accounting entry:
- `AccountDebited` - Debit entry (decreases asset accounts, increases liability/expense)
- `AccountCredited` - Credit entry (increases asset/revenue accounts, decreases liability)

**Transaction** - Groups related entries that must balance (debits = credits)

**Events**:
- `DebitEntryRegistered` - Published when account is debited
- `CreditEntryRegistered` - Published when account is credited

## Account Types

| Type | Double-Entry | Description |
|------|--------------|-------------|
| `ASSET` | ✅ | Resources owned (cash, inventory) |
| `LIABILITY` | ✅ | Debts and obligations |
| `REVENUE` | ✅ | Income from operations |
| `EXPENSE` | ✅ | Costs of operations |
| `OFF_BALANCE` | ❌ | Informational accounts outside balance sheet |

## Usage Examples

### Creating Accounts

```php
use SoftwareArchetypes\Accounting\Application\AccountingFacade;
use SoftwareArchetypes\Accounting\Domain\AccountType;
use SoftwareArchetypes\Accounting\Domain\AccountId;
use SoftwareArchetypes\Accounting\Common\Money;

$facade = new AccountingFacade($accountRepository, $eventsPublisher);

// Create cash account
$cashAccount = $facade->createAccount(
    AccountId::fromString('cash-001'),
    AccountType::ASSET,
    'Cash',
    Money::of(10000)
);

// Create revenue account
$revenueAccount = $facade->createAccount(
    AccountId::fromString('revenue-001'),
    AccountType::REVENUE,
    'Sales Revenue'
);
```

### Transferring Money

```php
$facade->transfer(
    AccountId::fromString('checking-001'),
    AccountId::fromString('savings-001'),
    Money::of(500),
    new DateTimeImmutable()
);
```

### Querying Balances

```php
$balance = $facade->balance(AccountId::fromString('cash-001'));
echo "Balance: " . $balance->amount();
```

## Architecture

### Layers

```
┌─────────────────────────────────────┐
│         Application                 │  AccountingFacade
│  (Use cases, orchestration)         │
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│           Domain                    │  Account, Entry, Events
│  (Business logic, entities)         │  AccountRepository interface
└─────────────────────────────────────┘
              ↓
┌─────────────────────────────────────┐
│       Infrastructure                │  InMemoryAccountRepository
│  (Technical implementations)        │  InMemoryEventsPublisher
└─────────────────────────────────────┘
```

### Dependency Rules

- **Domain** - No dependencies (core business logic)
- **Events** - Depends on Domain
- **Application** - Depends on Domain, Events, Infrastructure
- **Infrastructure** - Depends on Domain, Events

These rules are enforced by Deptrac architecture validation.

## Testing

### Run All Tests

```bash
composer test
```

### Run Unit Tests Only

```bash
composer test:unit
```

### Run Integration Tests Only

```bash
composer test:integration
```

## Code Quality

### Static Analysis

```bash
composer phpstan
```

PHPStan runs at maximum level ensuring complete type safety.

### Architecture Validation

```bash
composer deptrac
```

Validates that layer dependencies follow clean architecture principles using `classLike` collectors.

### Full CI Pipeline

```bash
composer ci
```

Runs PHPStan, Deptrac, unit tests, and integration tests in sequence.

## Implementation Details

### Double-Entry Bookkeeping

The system enforces double-entry bookkeeping where:
- Debit entries have negative amounts (decrease assets, increase expenses)
- Credit entries have positive amounts (increase assets, decrease expenses)
- All transactions must balance (sum of all entries = 0)

### Event Sourcing

Domain events are published for all state changes:
```php
$account->addEntry($debitEntry);
$events = $account->pendingEvents(); // [DebitEntryRegistered]
$account->clearPendingEvents();
```

### Balance Calculation

Balances are calculated as the sum of all entry amounts:
```php
$balance = Money::zero();
foreach ($entries as $entry) {
    $balance = $balance->add($entry->amount());
}
```

## Tech Stack

- **PHP**: 8.4+
- **PHPUnit**: 11.x (testing)
- **PHPStan**: Level max (static analysis)
- **Deptrac**: 3.x (architecture validation)

## Development

### Requirements

- PHP 8.4 or higher
- Composer 2.x

### Install Dependencies

```bash
composer install
```

### Run Quality Checks

```bash
# Check code style
composer cs-check

# Fix code style
composer cs-fix

# Run all quality checks
composer ci
```

## Credits

Ported from the [Software Archetypes](https://github.com/Software-Archetypes/archetypes) accounting module originally implemented in Java.

## License

MIT License
