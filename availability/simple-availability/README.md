# Simple Availability Pattern - PHP Port

A PHP 8.4 implementation of the Simple Availability pattern from the [Software Archetypes](https://github.com/Software-Archetypes/archetypes) project.

## Overview

The Simple Availability pattern manages asset availability through a lock-based system. Assets can be in different states (maintenance, available, locked, withdrawn) with specific rules governing state transitions.

## Features

- **Domain-Driven Design**: Clean separation of domain logic, application services, and infrastructure
- **Event-Driven Architecture**: Domain events for all state changes
- **Functional Error Handling**: Result monad for explicit success/failure handling
- **Type Safety**: Full PHP 8.4 type system usage with readonly classes
- **Rich Domain Model**: Aggregate root with business rules enforcement

## Installation

```bash
composer require software-archetypes/simple-availability
```

## Core Concepts

### Asset Availability States

1. **Maintenance Lock** - Initial state when asset is registered
2. **Available** - Asset activated and ready for use
3. **Owner Lock** - Time-bounded lock by a specific owner
4. **Withdrawal Lock** - Asset withdrawn from availability

### Domain Model

#### Value Objects
- `AssetId` - Unique identifier for assets
- `OwnerId` - Unique identifier for owners

#### Aggregate Root
- `AssetAvailability` - Main aggregate managing availability rules

#### Lock Types
- `MaintenanceLock` - Initial maintenance state
- `WithdrawalLock` - Withdrawn state
- `OwnerLock` - Time-bounded owner lock

### Commands

- `Register` - Register a new asset
- `Activate` - Activate asset (remove maintenance lock)
- `Withdraw` - Withdraw asset from availability
- `Lock` - Lock asset for specified duration
- `LockIndefinitely` - Extend existing lock to 365 days
- `Unlock` - Release asset lock

### Domain Events

Success events:
- `AssetRegistered`
- `AssetActivated`
- `AssetLocked`
- `AssetUnlocked`
- `AssetWithdrawn`
- `AssetLockExpired`

Rejection events:
- `AssetRegistrationRejected`
- `AssetActivationRejected`
- `AssetLockRejected`
- `AssetUnlockingRejected`
- `AssetWithdrawalRejected`

## Usage Examples

### Basic Workflow

```php
use SoftwareArchetypes\Availability\SimpleAvailability\Application\AvailabilityService;
use SoftwareArchetypes\Availability\SimpleAvailability\Domain\AssetId;
use SoftwareArchetypes\Availability\SimpleAvailability\Domain\OwnerId;
use SoftwareArchetypes\Availability\SimpleAvailability\Infrastructure\Repository\InMemoryAssetAvailabilityRepository;
use SoftwareArchetypes\Availability\SimpleAvailability\Infrastructure\EventPublisher\InMemoryDomainEventsPublisher;

// Setup
$repository = new InMemoryAssetAvailabilityRepository();
$eventsPublisher = new InMemoryDomainEventsPublisher();
$service = new AvailabilityService($repository, $eventsPublisher);

// Register new asset
$assetId = AssetId::of('asset-123');
$result = $service->registerAssetWith($assetId);

if ($result->isSuccess()) {
    echo "Asset registered successfully\n";
}

// Activate asset
$result = $service->activate($assetId);

// Lock asset
$ownerId = OwnerId::of('owner-456');
$duration = new DateInterval('PT1H'); // 1 hour
$result = $service->lock($assetId, $ownerId, $duration);

if ($result->isSuccess()) {
    echo "Asset locked successfully\n";
}

// Unlock asset
$result = $service->unlock($assetId, $ownerId, new DateTimeImmutable());
```

### Working with Domain Model Directly

```php
use SoftwareArchetypes\Availability\SimpleAvailability\Domain\AssetAvailability;
use SoftwareArchetypes\Availability\SimpleAvailability\Domain\AssetId;
use SoftwareArchetypes\Availability\SimpleAvailability\Domain\OwnerId;

$asset = AssetAvailability::of(AssetId::of('asset-123'));

// Activate
$result = $asset->activate();
$result->fold(
    fn($event) => echo "Activated: " . $event->getType(),
    fn($event) => echo "Rejected: " . $event->getReason()
);

// Lock for 30 minutes
$ownerId = OwnerId::of('owner-456');
$result = $asset->lockFor($ownerId, new DateInterval('PT30M'));

// Check result
if ($result->isSuccess()) {
    $event = $result->getSuccess();
    echo "Locked until: " . $event->getTo()->format('Y-m-d H:i:s');
}
```

### Result Monad Usage

```php
$result = $service->activate($assetId);

// Map success value
$mapped = $result->map(fn($event) => $event->getType());

// Fold to single value
$message = $result->fold(
    fn($success) => "Success: {$success->getType()}",
    fn($failure) => "Error: {$failure->getReason()}"
);

// Side effects
$result->peekSuccess(fn($event) => $logger->info("Asset activated"))
       ->peekFailure(fn($event) => $logger->error("Activation failed"));
```

## Business Rules

1. **Registration**: Asset must not exist
2. **Activation**: Only assets with MaintenanceLock can be activated
3. **Locking**: Only available (not locked) assets can be locked
4. **Lock Extension**: Only owner with existing lock can extend indefinitely
5. **Unlocking**: Only the lock owner can unlock
6. **Withdrawal**: Only available or maintenance assets can be withdrawn

## Architecture

```
src/
├── Application/          # Application services
│   ├── AvailabilityService.php
│   └── OverdueLockHandling.php
├── Commands/            # Command objects
├── Common/              # Shared utilities (Result monad)
├── Domain/              # Domain model
│   ├── AssetAvailability.php (Aggregate Root)
│   ├── AssetId.php
│   ├── OwnerId.php
│   └── Lock types
├── Events/              # Domain events
└── Infrastructure/      # Infrastructure implementations
    ├── Repository/
    └── EventPublisher/
```

## Testing

```bash
# Run tests
composer test

# Run with coverage
composer test-coverage

# Run PHPStan
composer phpstan

# Check code style
composer cs-check

# Fix code style
composer cs-fix
```

## PHP 8.4 Features Used

- **Readonly classes**: Immutable value objects and events
- **Property hooks**: (when available)
- **Constructor property promotion**: Concise class definitions
- **Named arguments**: Clear method calls
- **Enums**: Type-safe constants
- **Generics via PHPDoc**: Type-safe Result monad

## Differences from Java Implementation

1. **DateInterval vs Duration**: PHP uses `DateInterval` instead of Java's `Duration`
2. **Nullable types**: PHP uses `?Type` instead of Java's `Optional<T>`
3. **Array storage**: In-memory repository uses PHP arrays
4. **No sealed classes**: PHP doesn't have sealed classes (yet), using interfaces instead
5. **Closure syntax**: PHP closures use `fn()` and `function()` instead of Java lambdas

## Contributing

Contributions are welcome! Please ensure:
- All tests pass
- Code follows PSR-12 standards
- PHPStan level max passes
- New features include tests and documentation

## License

MIT License - See LICENSE file for details

## Credits

This is a PHP port of the original Java implementation from the [Software Archetypes](https://github.com/Software-Archetypes/archetypes) project.

## Related Patterns

- **Timed Availability**: Time-slot based availability
- **Capacity**: Quantity-based resource management
- **Reservation**: Booking and reservation systems
