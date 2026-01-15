# Software Archetypes - PHP Examples

A collection of PHP implementations of software archetypes from the [Software Archetypes](https://github.com/Software-Archetypes/archetypes) project.

## Overview

This repository contains PHP 8.4 ports of various software architecture patterns demonstrating:
- Domain-Driven Design (DDD)
- Event-Driven Architecture
- Clean Architecture principles
- Functional error handling with Result monads
- Full type safety with PHP 8.4

## Archetypes

### [Accounting](accounting)

Double-entry bookkeeping accounting system.

**Features:**
- Double-entry bookkeeping with balanced transactions
- Multiple account types (Asset, Liability, Revenue, Expense, Off-Balance)
- Transaction processing with automatic balance updates
- Event-driven architecture
- Clean architecture with Domain, Application, and Infrastructure layers

**Tech Stack:** PHP 8.4, PHPUnit, PHPStan (level max), Deptrac

[→ Read more](accounting/README.md)

### Availability

#### [Simple Availability](availability/simple-availability)
Lock-based asset availability management pattern.

**Features:**
- Asset state management (Maintenance, Available, Locked, Withdrawn)
- Time-bounded owner locks
- Event-driven state transitions
- Business rule enforcement

**Tech Stack:** PHP 8.4, PHPUnit, PHPStan (level max), Deptrac

[→ Read more](availability/simple-availability/README.md)

#### [Timed Availability](availability/timed-availability)
Advanced time-slot based resource availability management.

**Features:**
- Time-slot based resource booking
- Resource blocking and availability checks
- Calendar integration
- Event-driven state transitions

**Tech Stack:** PHP 8.4, PHPUnit, PHPStan (level max), Deptrac

[→ Read more](availability/timed-availability/README.md)

### [Party](party)

Flexible entity modeling pattern for managing persons and organizations with dynamic roles.

**Features:**
- Universal representation for persons and organizations
- Dynamic role management (Customer, Supplier, Partner, etc.)
- Registered identifiers support (tax numbers, passports)
- Event-driven architecture
- Result monad for functional error handling
- Optimistic locking support

**Tech Stack:** PHP 8.4, PHPUnit, PHPStan (level max), Deptrac

[→ Read more](party/README.md)

### [Pricing](pricing)

Flexible pricing calculation system for financial applications.

**Features:**
- Multiple calculator types (Fixed, Interest-based)
- Extensible calculator architecture
- Type-safe parameter handling
- Precise decimal calculations with brick/math
- Repository pattern for calculator management
- Support for various time periods (daily, monthly, yearly)

**Tech Stack:** PHP 8.4, PHPUnit, PHPStan (level max), Deptrac, Brick Math

[→ Read more](pricing/README.md)

### [Product](product)

Comprehensive product catalog and instance management with features and tracking.

**Features:**
- Product type definitions with configurable features
- Multiple tracking strategies (serial numbers, batches, or both)
- Dynamic feature system with constraint validation
- Support for GTIN, ISBN, UUID identifiers
- Serial number validation (IMEI, VIN, custom formats)
- Batch management with production and expiry dates
- Builder pattern for complex object construction
- Clean architecture with DDD principles

**Tech Stack:** PHP 8.4, PHPUnit, PHPStan (level max), Deptrac, Brick Math

[→ Read more](product/README.md)

### [Quantity](quantity)

Value objects for quantities and measurements with units.

**Features:**
- Money value object for financial calculations
- Quantity with unit of measure support
- Type-safe operations
- Currency handling

**Tech Stack:** PHP 8.4, PHPUnit, PHPStan (level max), Deptrac

## Development

### Requirements

- PHP 8.4+
- Composer 2.x

### Testing

Each archetype includes comprehensive testing:

```bash
cd availability/simple-availability

# Run all tests
composer test

# Run only unit tests
composer test:unit

# Run only integration tests
composer test:integration
```

### Code Quality

Each archetype maintains high code quality standards:

```bash
# Static analysis (PHPStan level max)
composer phpstan

# Architecture validation
composer deptrac

# Code style check
composer cs-check

# Code style fix
composer cs-fix

# Run full CI pipeline locally
composer ci
```

## Continuous Integration

The repository uses GitHub Actions for automated testing across all archetypes.

### CI Pipeline

For each archetype, the following steps run in order:
1. **PHPStan** - Static analysis at maximum level
2. **Deptrac** - Architecture layer dependency validation
3. **PHPUnit (Unit)** - Fast unit tests
4. **PHPUnit (Integration)** - Full integration tests

See [CI Documentation](.github/workflows/README.md) for details.

## Project Structure

```
.
├── .github/
│   └── workflows/          # GitHub Actions CI configuration
├── accounting/             # Accounting archetype
├── availability/           # Availability archetypes
│   ├── simple-availability/
│   └── timed-availability/
├── party/                  # Party archetype
├── pricing/                # Pricing archetype
├── product/                # Product archetype
├── quantity/               # Quantity archetype
└── [future archetypes]/
```

## Contributing

Contributions are welcome! When adding new archetypes:

1. Follow the existing structure pattern
2. Include comprehensive tests (unit + integration)
3. Configure PHPStan at level max
4. Set up Deptrac for architecture validation
5. Add your archetype to the CI matrix in `.github/workflows/ci.yml`
6. Update this README

## Standards

All archetypes must maintain:
- ✅ PSR-12 code style
- ✅ PHPStan level max compliance
- ✅ No architecture violations (Deptrac)
- ✅ 100% test pass rate
- ✅ Comprehensive documentation

## Credits

This repository contains PHP ports of patterns from the [Software Archetypes](https://github.com/Software-Archetypes/archetypes) project.

Original implementations in Java by the Software Archetypes community.

## License

MIT License - See individual archetype directories for specific license files.
