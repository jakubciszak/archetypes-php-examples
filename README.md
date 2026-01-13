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
├── availability/           # Availability archetypes
│   └── simple-availability/
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
