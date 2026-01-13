# Timed Availability Pattern - PHP Implementation

[![Tests](https://img.shields.io/badge/tests-50%20passing-brightgreen)]()
[![PHPStan](https://img.shields.io/badge/PHPStan-level%20max-brightgreen)]()
[![Deptrac](https://img.shields.io/badge/Deptrac-0%20violations-brightgreen)]()

PHP 8.4+ implementation of the **Timed Availability** archetype, ported from the [Software Archetypes Java project](https://github.com/Software-Archetypes/archetypes).

## Overview

The Timed Availability pattern solves the challenge of managing exclusive access to shared resources across time periods. It prevents double-booking by abstracting availability as a generic temporal model where resources can be blocked, released, or disabled during defined time windows.

### Key Features

- **Time-based Resource Management**: Block and release resources for specific time periods
- **Segment-based Conflict Prevention**: Automatically splits time slots into segments (default: 60 minutes) to ensure database-level conflict detection
- **Optimistic Locking**: Version-based concurrency control for high-performance scenarios
- **Three States Management**: Available, Blocked, Disabled
- **Calendar Views**: User-friendly read models with aggregated time slots
- **Event Publishing**: Domain events for state changes

## Installation

```bash
composer require software-archetypes/timed-availability
```

## Core Concepts

### ResourceAvailability Aggregate

The main domain aggregate managing temporal access to resources:

```php
$availability = new ResourceAvailability(
    ResourceAvailabilityId::newOne(),
    $resourceId,
    $timeSlot
);

// Block the resource
$availability->block($owner);

// Release when done
$availability->release($owner);

// Disable for maintenance
$availability->disable($maintenanceOwner);
```

### Blockade Value Object

Encapsulates three availability states:
- **Available**: No owner, not disabled
- **Blocked**: Owned by specific owner
- **Disabled**: Taken over for maintenance/relocation

```php
$blockade = Blockade::none();        // Available
$blockade = Blockade::ownedBy($owner); // Blocked
$blockade = Blockade::disabledBy($owner); // Disabled
```

### Time Segmentation

Time slots are automatically normalized and split into uniform segments to prevent overlapping bookings:

```php
// Request: 10:05-11:30 with 60-minute segments
// Normalized to: 10:00-12:00
// Split into: [10:00-11:00, 11:00-12:00]

$segments = Segments::split($timeSlot, SegmentInMinutes::of(60));
```

## Usage

### Basic Resource Blocking

```php
use SoftwareArchetypes\Availability\TimedAvailability\Application\AvailabilityFacade;
use SoftwareArchetypes\Availability\TimedAvailability\Domain\ResourceId;
use SoftwareArchetypes\Availability\TimedAvailability\Domain\Owner;
use SoftwareArchetypes\Availability\TimedAvailability\Domain\TimeSlot;

// Create time slots for a resource
$resourceId = ResourceId::newOne();
$timeSlot = TimeSlot::createDailyTimeSlotAtUTC(2024, 1, 15);

$facade->createResourceSlots($resourceId, $timeSlot);

// Block a time slot
$owner = Owner::newOne();
$requestedSlot = new TimeSlot(
    new \DateTimeImmutable('2024-01-15 10:00:00'),
    new \DateTimeImmutable('2024-01-15 12:00:00')
);

$success = $facade->block($resourceId, $requestedSlot, $owner);

// Release the slot
$facade->release($resourceId, $requestedSlot, $owner);
```

### Disabling Resources

```php
// Disable for maintenance
$maintenanceOwner = Owner::newOne();
$facade->disable($resourceId, $timeSlot, $maintenanceOwner);

// Only the maintenance owner can re-enable
$facade->enable($resourceId, $timeSlot, $maintenanceOwner);
```

### Loading Calendar Views

```php
// Get aggregated calendar for a resource
$calendar = $facade->loadCalendar($resourceId, $timeSlot);

// Available slots
$availableSlots = $calendar->availableSlots();

// Slots taken by specific owner
$mySlots = $calendar->takenBy($owner);
```

### Random Resource Selection

```php
// Find and block any available resource from a pool
$resourceIds = [
    ResourceId::newOne(),
    ResourceId::newOne(),
    ResourceId::newOne(),
];

$selectedResource = $facade->blockRandomAvailable(
    $resourceIds,
    $timeSlot,
    $owner
);
```

## Architecture

This implementation follows **Domain-Driven Design** principles with clean architecture:

```
src/
├── Domain/              # Core business logic
│   ├── ResourceAvailability.php
│   ├── Blockade.php
│   ├── TimeSlot.php
│   └── ...
├── Segment/             # Time segmentation logic
│   ├── Segments.php
│   ├── SegmentInMinutes.php
│   └── ...
├── Events/              # Domain events
│   ├── ResourceTakenOver.php
│   └── ...
├── Application/         # Use cases and facades
│   ├── AvailabilityFacade.php
│   ├── Calendar.php
│   └── ...
├── Infrastructure/      # Technical implementations
│   ├── InMemoryResourceAvailabilityRepository.php
│   └── ...
└── Common/              # Shared utilities
    ├── Clock.php
    └── ...
```

### Layer Dependencies

- **Common**: No dependencies
- **Domain**: Depends on Common, Events, Segment
- **Segment**: Depends on Domain, Common
- **Events**: Depends on Domain, Common
- **Application**: Depends on Domain, Events, Segment, Common
- **Infrastructure**: Depends on Application, Domain, Events, Common

Architecture validated with **Deptrac**.

## Development

### Running Tests

```bash
# All tests
composer test

# Unit tests only
composer test:unit

# With coverage
composer test-coverage
```

### Static Analysis

```bash
# PHPStan (level: max)
composer phpstan

# Deptrac (architecture validation)
composer deptrac
```

### Code Quality

```bash
# Fix code style
composer cs-fix

# Check code style
composer cs-check
```

### CI Pipeline

```bash
# Run all checks (PHPStan, Deptrac, Tests)
composer ci
```

## Testing

The implementation includes **50 unit tests** covering:

- **TimeSlot**: Overlap detection, normalization, common parts
- **Blockade**: State transitions, ownership validation
- **Segments**: Time slot splitting and normalization
- **ResourceAvailability**: Block, release, disable operations
- **Segment Classes**: Normalization and splitting algorithms

All tests use the **Test-Driven Development (TDD)** approach.

## Key Design Patterns

### State Pattern
- **Blockade** encapsulates three states (available, blocked, disabled)
- State transitions enforce business rules

### Repository Pattern
- **ResourceAvailabilityRepository** abstracts persistence
- Supports optimistic locking with version checking

### Facade Pattern
- **AvailabilityFacade** provides simplified API
- Hides complexity of segmentation and normalization

### Value Objects
- Immutable: **TimeSlot**, **Blockade**, **Owner**, **ResourceId**
- Encapsulate validation and business logic

### Domain Events
- **ResourceTakenOver** published when resource is disabled
- Enables event-driven architecture

## Requirements

- PHP 8.4+
- ramsey/uuid ^4.7

## Development Dependencies

- PHPUnit 11.0+
- PHPStan 2.0+ (level: max)
- Deptrac 3.0+
- PHP-CS-Fixer 3.64+

## License

MIT

## Credits

This is a PHP port of the [Software Archetypes project](https://github.com/Software-Archetypes/archetypes), originally implemented in Java.

## Related Patterns

- **Simple Availability**: Simpler variant without time segmentation
- **Booking**: Higher-level pattern using Timed Availability

## Contributing

Contributions are welcome! Please ensure:
- All tests pass (`composer test`)
- PHPStan passes (`composer phpstan`)
- Deptrac passes (`composer deptrac`)
- Code follows PSR-12 (`composer cs-fix`)
