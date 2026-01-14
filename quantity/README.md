# Quantity Archetype - PHP Implementation

A PHP 8.4 implementation of the Quantity archetype pattern from the [Software Archetypes](https://github.com/Software-Archetypes/archetypes) project.

## Overview

The Quantity archetype provides a robust model for representing measured amounts with their units of measurement. This pattern solves the problem of safely handling quantities in business applications where the unit of measurement is as important as the numeric value itself.

## Features

- **Type-Safe Measurements**: Enforces unit compatibility in arithmetic operations
- **Precision Handling**: Uses arbitrary-precision decimal arithmetic (BigDecimal)
- **Immutability**: All value objects are immutable for thread-safety
- **Rich Unit Library**: Predefined units for common measurements
- **Money Support**: Specialized implementation for monetary values
- **Validation**: Fail-fast validation with clear error messages

## Core Concepts

### The Quantity Pattern

A **Quantity** is an amount of something measured according to some standard of measurement. In simpler terms:

**Quantity = Number + Unit**

For example: "100 kilograms", "25.5 square meters", "1000 pieces"

This archetype ensures that:
- Operations only occur between compatible units
- Precision is maintained throughout calculations
- Business rules are enforced at the domain level

### Domain Model

The implementation consists of three main components:

#### Quantity
The core value object representing an amount with a unit:
- `BigDecimal amount` - Precise numeric value
- `Unit unit` - The unit of measurement
- Arithmetic operations (add, subtract)
- Comparison operations (isGreaterThan, isLessThan, equals)

#### Unit
Represents a unit of measurement:
- `string symbol` - Short form (e.g., "kg", "m²")
- `string name` - Full name (e.g., "kilograms", "square meters")
- Factory methods for common units
- Support for custom units

#### Money
Specialized quantity for monetary values:
- Currency-aware operations
- Additional financial operations (multiply, divide)
- Precision handling for currency calculations

## Usage Examples

### Creating Quantities

```php
use SoftwareArchetypes\Quantity\Quantity;
use SoftwareArchetypes\Quantity\Unit;
use Brick\Math\BigDecimal;

// Using predefined units
$weight = Quantity::of(100, Unit::kilograms());           // 100 kg
$volume = Quantity::of(50.5, Unit::liters());             // 50.5 l
$count = Quantity::of(1000, Unit::pieces());              // 1000 pcs
$area = Quantity::of('25.5', Unit::squareMeters());       // 25.5 m²
$capacity = Quantity::of(100, Unit::cubicMeters());       // 100 m³
$duration = Quantity::of(8, Unit::hours());               // 8 h

// Using BigDecimal for maximum precision
$precise = Quantity::of(BigDecimal::of('0.000001'), Unit::meters());

// Custom units
$pallets = Unit::of('plt', 'pallets');
$stock = Quantity::of(45, $pallets);                      // 45 plt
```

### Arithmetic Operations

```php
// Addition (requires same unit)
$initial = Quantity::of(100, Unit::kilograms());
$added = Quantity::of(50, Unit::kilograms());
$total = $initial->add($added);                           // 150 kg

// Subtraction (requires same unit)
$remaining = $total->subtract(Quantity::of(30, Unit::kilograms())); // 120 kg

// Unit mismatch protection
$weight = Quantity::of(100, Unit::kilograms());
$volume = Quantity::of(50, Unit::liters());
$invalid = $weight->add($volume);                         // Throws InvalidArgumentException
```

### Comparison Operations

```php
$stock = Quantity::of(150, Unit::pieces());
$minimum = Quantity::of(200, Unit::pieces());
$critical = Quantity::of(50, Unit::pieces());

// Comparisons
$stock->isLessThan($minimum);                            // true
$stock->isGreaterThan($critical);                        // true
$stock->isZero();                                        // false

// Equality
$q1 = Quantity::of(100, Unit::kilograms());
$q2 = Quantity::of(100, Unit::kilograms());
$q1->equals($q2);                                        // true
```

### Working with Money

```php
use SoftwareArchetypes\Quantity\Money\Money;

// Creating money
$price = Money::pln(100);                                // PLN 100
$cost = Money::pln(BigDecimal::of('99.99'));             // PLN 99.99
$zero = Money::zeroPln();                                // PLN 0
$one = Money::onePln();                                  // PLN 1

// Arithmetic
$total = Money::pln(100)->add(Money::pln(50));           // PLN 150
$change = Money::pln(100)->subtract(Money::pln(25));     // PLN 75
$negated = Money::pln(50)->negate();                     // PLN -50
$absolute = Money::pln(-50)->abs();                      // PLN 50

// Multiplication and division
$unitPrice = Money::pln(25);
$totalCost = $unitPrice->multiply(10);                   // PLN 250
$share = Money::pln(100)->divide(3);                     // PLN 33.33 (rounded)

// Comparisons
$budget = Money::pln(100);
$expense = Money::pln(75);
$expense->isLessThan($budget);                           // true
$expense->isGreaterThan(Money::pln(50));                 // true

// Aggregation
$min = Money::min(Money::pln(100), Money::pln(50));      // PLN 50
$max = Money::max(Money::pln(100), Money::pln(50));      // PLN 100
```

### Real-World Scenarios

#### Inventory Management
```php
$initialStock = Quantity::of(1000, Unit::pieces());
$orderReceived = Quantity::of(500, Unit::pieces());
$newStock = $initialStock->add($orderReceived);

$shipmentSent = Quantity::of(200, Unit::pieces());
$finalStock = $newStock->subtract($shipmentSent);        // 1300 pcs
```

#### Weight Calculations
```php
$productA = Quantity::of('2.5', Unit::kilograms());
$productB = Quantity::of('3.75', Unit::kilograms());
$productC = Quantity::of('1.25', Unit::kilograms());

$totalWeight = $productA->add($productB)->add($productC); // 7.5 kg

$shippingLimit = Quantity::of(10, Unit::kilograms());
if ($totalWeight->isLessThan($shippingLimit)) {
    // Can ship in one package
}
```

#### Financial Calculations
```php
$price = Money::pln('99.99');
$taxRate = '0.23'; // 23% VAT

$taxAmount = $price->multiply($taxRate);
$totalPrice = $price->add($taxAmount);                   // PLN 122.9877

// Round for display
$displayPrice = Money::pln(
    $totalPrice->value()->toScale(2, RoundingMode::HALF_UP)
);                                                        // PLN 122.99
```

## Predefined Units

The library includes common units out of the box:

| Unit | Symbol | Factory Method |
|------|--------|----------------|
| Pieces | pcs | `Unit::pieces()` |
| Kilograms | kg | `Unit::kilograms()` |
| Liters | l | `Unit::liters()` |
| Meters | m | `Unit::meters()` |
| Square Meters | m² | `Unit::squareMeters()` |
| Cubic Meters | m³ | `Unit::cubicMeters()` |
| Hours | h | `Unit::hours()` |
| Minutes | min | `Unit::minutes()` |

### Creating Custom Units

```php
$celsius = Unit::of('℃', 'degrees Celsius');
$fahrenheit = Unit::of('℉', 'degrees Fahrenheit');
$widgets = Unit::of('widget', 'widgets');
$barrels = Unit::of('bbl', 'barrels');
```

## Business Rules

1. **Unit Compatibility**: Arithmetic operations require matching units
2. **Non-Negativity**: Quantities cannot be negative (except Money)
3. **Precision**: BigDecimal ensures accurate decimal arithmetic
4. **Immutability**: All operations return new instances
5. **Fail-Fast Validation**: Invalid operations throw exceptions immediately

## Architecture

```
src/
├── Quantity.php              # Core quantity value object
├── Unit.php                  # Unit of measurement value object
└── Money/
    └── Money.php            # Monetary quantity specialization

tests/
├── Domain/
│   ├── QuantityTest.php     # Unit tests for Quantity
│   ├── UnitTest.php         # Unit tests for Unit
│   └── Money/
│       └── MoneyTest.php    # Unit tests for Money
└── Integration/
    └── QuantityIntegrationTest.php  # Integration scenarios
```

### Layer Dependencies (Deptrac)

- **Core**: Quantity, Unit (no dependencies)
- **Money**: Depends on Core
- **Common**: Shared utilities (if needed)

## Testing

```bash
# Run all tests
composer test

# Run unit tests only
composer test:unit

# Run integration tests only
composer test:integration

# Run with coverage
composer test-coverage
```

## Code Quality

```bash
# Static analysis (PHPStan level max)
composer phpstan

# Architecture validation
composer deptrac

# Code style check (PSR-12)
composer phpcs

# Code style fix
composer phpcs-fix

# Run full CI pipeline
composer ci
```

## Key Design Patterns

### Value Object Pattern
- Immutable by design
- Equality based on value, not identity
- No lifecycle or identity concerns

### Type Safety
- Strong typing prevents unit mismatches
- Compile-time and runtime validation
- Clear error messages

### Fail-Fast Validation
- Constructor validation ensures invariants
- Operations validate compatibility
- Explicit exception messages

### Precision Handling
```php
// Uses BigDecimal internally
$q1 = Quantity::of('0.000001', Unit::meters());
$q2 = Quantity::of('0.000002', Unit::meters());
$result = $q1->add($q2);  // Maintains full precision: 0.000003 m
```

## PHP 8.4 Features Used

- **Readonly classes**: Enforced immutability
- **Constructor property promotion**: Concise class definitions
- **Union types**: Flexible input types (int|float|string|BigDecimal)
- **Named arguments**: Clear method calls
- **Strict types**: Type safety throughout

## Differences from Java Implementation

1. **No sealed classes**: PHP uses final classes instead
2. **Readonly properties**: PHP 8.4 readonly replaces Java's final
3. **Union types**: PHP uses `int|float|string` vs Java method overloading
4. **BigDecimal library**: Uses brick/math instead of Java's built-in
5. **Array types**: PHP uses arrays instead of Java's List/Set

## Tech Stack

- **PHP**: 8.4+
- **brick/math**: 0.12+ (arbitrary-precision arithmetic)
- **PHPUnit**: 11.x (testing)
- **PHPStan**: Level max (static analysis)
- **Deptrac**: 3.x (architecture validation)
- **PHP_CodeSniffer**: 3.x (PSR-12 style)

## Development

### Requirements

- PHP 8.4 or higher
- Composer 2.x

### Install Dependencies

```bash
cd quantity
composer install
```

### Run Quality Checks

```bash
# Run all checks
composer ci

# Individual checks
composer phpcs      # Code style
composer phpstan    # Static analysis
composer deptrac    # Architecture
composer test:unit  # Unit tests
```

## Use Cases

The Quantity archetype is ideal for:

- **Inventory Management**: Stock levels, reorder points
- **Warehouse Systems**: Storage capacity, volume calculations
- **E-commerce**: Product quantities, shipping weights
- **Manufacturing**: Raw material quantities, production volumes
- **Logistics**: Cargo weights, container volumes
- **Financial Systems**: Monetary amounts with precision
- **Time Tracking**: Work hours, project durations
- **Recipe Management**: Ingredient quantities
- **Healthcare**: Medication doses, patient measurements

## Why Use This Pattern?

### Type Safety
```php
// Prevents logical errors
$weight = Quantity::of(100, Unit::kilograms());
$volume = Quantity::of(50, Unit::liters());
$weight->add($volume);  // Exception: Cannot add kg and l
```

### Precision
```php
// Maintains accuracy in calculations
$price = Money::pln('0.01');
$quantity = 100;
$total = $price->multiply($quantity);  // Exactly PLN 1.00
```

### Expressiveness
```php
// Self-documenting code
$capacity = Quantity::of(5000, Unit::liters());
$currentLevel = Quantity::of(3500, Unit::liters());
$remainingCapacity = $capacity->subtract($currentLevel);
// Much clearer than: $remaining = 5000 - 3500;
```

### Maintainability
```php
// Business rules in the domain
if ($stock->isLessThan($minimumLevel)) {
    $this->triggerReorder();
}
```

## Credits

Ported from the [Software Archetypes](https://github.com/Software-Archetypes/archetypes) quantity module originally implemented in Java.

Original concept from "Enterprise Patterns and MDA: Building Better Software with Archetype Patterns and UML" by Jim Arlow and Ila Neustadt.

Original Java implementation authors: Bartłomiej Słota and Jakub Pilimon

## License

MIT License - Feel free to use in your projects

## Related Patterns

- **Money**: Specialized quantity for currency (included in this package)
- **Product**: Domain entities that have quantities
- **Order**: Aggregates with order line items and quantities
- **Inventory**: Stock levels and movements
- **Unit Conversion**: Extension for converting between compatible units

## Contributing

This is a port of the Java implementation. Please refer to the original [Software Archetypes](https://github.com/Software-Archetypes/archetypes) project for architectural decisions and pattern discussions.

## Further Reading

- [Enterprise Patterns and MDA](https://www.oreilly.com/library/view/enterprise-patterns-and/032111230X/ch10.html) - Chapter 10: Quantity Archetype Pattern
- [Software Archetypes Project](https://github.com/Software-Archetypes/archetypes)
- [Domain-Driven Design](https://www.domainlanguage.com/ddd/) - Value Objects and Ubiquitous Language
