# Pricing Archetype

Flexible pricing calculation pattern implementation in PHP, ported from the [Software Archetypes](https://github.com/Software-Archetypes/archetypes) project.

## Overview

The **Pricing Archetype** provides a flexible and extensible system for calculating prices, fees, and interest rates. It allows you to define multiple calculator types, each with different calculation logic, and manage them through a unified facade.

This pattern is commonly used in:
- **Financial systems** - calculating interest rates, loan payments, fees
- **E-commerce platforms** - dynamic pricing, discounts, promotions
- **Subscription services** - tiered pricing, usage-based billing
- **Insurance** - premium calculations
- **SaaS applications** - feature-based pricing models

## Features

- **Multiple calculator types**: Support for fixed-price and interest-based calculations
- **Extensible design**: Easy to add new calculator types
- **Type-safe operations**: Full PHP 8.4 type safety with enums
- **Precise calculations**: Using brick/math for accurate financial calculations
- **Repository pattern**: Abstract storage for calculators
- **Facade pattern**: Simple API for managing and using calculators

## Architecture

The pricing archetype follows Clean Architecture principles:

```
┌─────────────────────────────────────┐
│         PricingFacade               │
│     (Application Entry Point)       │
└──────────┬──────────────────────────┘
           │
           ├──► CalculatorRepository
           │    (Port/Interface)
           │
           └──► Calculator Implementations
                ├─ SimpleFixedCalculator
                └─ SimpleInterestCalculator
```

### Core Components

#### Calculator Interface
Defines the contract for all pricing calculators:
- `calculate()` - Performs the calculation
- `describe()` - Provides human-readable description
- `getType()` - Returns calculator type
- `getId()` - Unique calculator identifier
- `name()` - Calculator name

#### Calculator Types

##### SimpleFixedCalculator
Returns a fixed amount regardless of input parameters.

**Use cases:**
- Monthly subscription fees
- One-time setup costs
- Flat-rate charges

**Example:**
```php
$calculator = new SimpleFixedCalculator('monthly-fee', BigDecimal::of('29.99'));
$result = $calculator->calculate(Parameters::empty());
// Returns Money::pln('29.99')
```

##### SimpleInterestCalculator
Calculates simple interest based on annual rate, base amount, and time period.

**Formula:** Interest = (Base × Rate × Time) / Period

**Use cases:**
- Loan interest calculations
- Savings account interest
- Late payment fees
- Credit card interest

**Supported time units:**
- Days (365 days per year)
- Weeks (52 weeks per year)
- Months (12 months per year)
- Years (1 year per year)

**Example:**
```php
$calculator = new SimpleInterestCalculator('loan-interest', BigDecimal::of('5'));
$params = new Parameters([
    'base' => Money::pln('10000'),
    'unit' => DateInterval::createFromDateString('1 year')
]);
$result = $calculator->calculate($params);
// Returns Money::pln('500.00') - 5% of 10,000 for 1 year
```

#### PricingFacade
Main entry point for the pricing system:
- `addCalculator()` - Register a new calculator
- `calculate()` - Calculate using a named calculator
- `availableCalculators()` - List all calculators
- `listCalculatorsWithDescriptions()` - Group calculators by type
- `availableCalculatorTypes()` - Get all calculator types

#### Parameters
Type-safe parameter container for calculator inputs.

## Installation

```bash
composer require software-archetypes/pricing
```

## Usage

### Basic Usage

```php
use SoftwareArchetypes\Pricing\CalculatorType;
use SoftwareArchetypes\Pricing\Infrastructure\InMemoryCalculatorRepository;
use SoftwareArchetypes\Pricing\Parameters;
use SoftwareArchetypes\Pricing\PricingFacade;
use Brick\Math\BigDecimal;

// Initialize the facade
$facade = new PricingFacade(new InMemoryCalculatorRepository());

// Add a fixed-price calculator
$facade->addCalculator(
    'premium-subscription',
    CalculatorType::SIMPLE_FIXED,
    new Parameters(['amount' => BigDecimal::of('99.99')])
);

// Calculate the price
$price = $facade->calculate('premium-subscription', Parameters::empty());
echo $price; // PLN 99.99
```

### Interest Calculation

```php
use DateInterval;
use SoftwareArchetypes\Quantity\Money\Money;

// Add an interest calculator (12% annual rate)
$facade->addCalculator(
    'credit-card-interest',
    CalculatorType::SIMPLE_INTEREST,
    new Parameters(['annualRate' => BigDecimal::of('12')])
);

// Calculate monthly interest on $5,000 balance
$monthlyInterest = $facade->calculate('credit-card-interest', new Parameters([
    'base' => Money::pln('5000'),
    'unit' => DateInterval::createFromDateString('1 month')
]));

echo $monthlyInterest; // PLN 50.00
```

### Multiple Calculators

```php
// Setup fee + recurring cost example
$facade->addCalculator(
    'setup-fee',
    CalculatorType::SIMPLE_FIXED,
    new Parameters(['amount' => BigDecimal::of('199')])
);

$facade->addCalculator(
    'monthly-cost',
    CalculatorType::SIMPLE_FIXED,
    new Parameters(['amount' => BigDecimal::of('49.99')])
);

$setupCost = $facade->calculate('setup-fee', Parameters::empty());
$monthlyCost = $facade->calculate('monthly-cost', Parameters::empty());
$firstMonthTotal = $setupCost->add($monthlyCost); // PLN 248.99
```

### Listing Available Calculators

```php
// Get all calculators
$calculators = $facade->availableCalculators();

foreach ($calculators as $calc) {
    echo "{$calc->name}: {$calc->description}\n";
}

// Group by type
$grouped = $facade->listCalculatorsWithDescriptions();

foreach ($grouped as $typeName => $calculators) {
    echo "\n{$typeName}:\n";
    foreach ($calculators as $calc) {
        echo "  - {$calc->name}: {$calc->description}\n";
    }
}
```

## Testing

### Run All Tests

```bash
composer test
```

### Run Specific Test Suites

```bash
# Unit tests only
composer test:unit

# Integration tests only
composer test:integration
```

### Code Coverage

```bash
composer test-coverage
```

## Code Quality

### Static Analysis

```bash
# PHPStan (level max)
composer phpstan

# Architecture validation
composer deptrac
```

### Code Style

```bash
# Check code style (PSR-12)
composer phpcs

# Fix code style issues
composer phpcs-fix
```

### Full CI Pipeline

```bash
composer ci
```

This runs all checks in order:
1. PHP CodeSniffer
2. PHPStan
3. Deptrac
4. Unit tests
5. Integration tests

## Extending the Archetype

### Adding a New Calculator Type

1. **Define the enum case** in `CalculatorType`:

```php
enum CalculatorType
{
    case SIMPLE_FIXED;
    case SIMPLE_INTEREST;
    case PERCENTAGE_DISCOUNT; // New type
}
```

2. **Implement the Calculator interface**:

```php
final readonly class PercentageDiscountCalculator implements Calculator
{
    public function __construct(
        private string $calculatorName,
        private BigDecimal $discountPercentage
    ) {
        $this->id = CalculatorId::generate();
    }

    public function calculate(Parameters $parameters): Money
    {
        $originalPrice = $parameters->get('originalPrice');
        $discount = $this->discountPercentage->dividedBy(100, 10, RoundingMode::HALF_UP);
        $discountAmount = $originalPrice->multiply($discount);

        return $originalPrice->subtract($discountAmount);
    }

    // Implement other interface methods...
}
```

3. **Update PricingFacade** to support the new type:

```php
private function createCalculator(string $name, CalculatorType $type, Parameters $parameters): Calculator
{
    return match ($type) {
        CalculatorType::SIMPLE_FIXED => new SimpleFixedCalculator(/*...*/),
        CalculatorType::SIMPLE_INTEREST => new SimpleInterestCalculator(/*...*/),
        CalculatorType::PERCENTAGE_DISCOUNT => new PercentageDiscountCalculator(/*...*/)
    };
}
```

## Best Practices

1. **Use appropriate calculator types**: Choose the calculator that matches your business logic
2. **Validate parameters**: Always check that required parameters are present before calculation
3. **Handle precision**: Use `BigDecimal` for all financial calculations to avoid floating-point errors
4. **Name calculators clearly**: Use descriptive names that indicate what the calculator does
5. **Test thoroughly**: Write tests for edge cases, especially around rounding and precision

## Dependencies

- **PHP 8.4+**: Uses enums, readonly properties, and modern PHP features
- **brick/math**: Arbitrary precision mathematics for accurate financial calculations
- **ramsey/uuid**: UUID generation for calculator identifiers
- **software-archetypes/quantity**: Money value object for type-safe currency handling

## License

MIT License - See LICENSE file for details.

## Credits

This is a PHP port of the Pricing archetype from the [Software Archetypes](https://github.com/Software-Archetypes/archetypes) project.

Original implementation in Java by the Software Archetypes community.
