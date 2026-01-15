# Product Archetype - PHP Implementation

A PHP 8.4 implementation of the Product archetype pattern from the [Software Archetypes](https://github.com/Software-Archetypes/archetypes) project.

## Overview

The Product archetype provides a flexible and extensible system for modeling products with configurable features, tracking strategies, and rich domain behaviors. This pattern solves the challenge of managing diverse product types with varying characteristics, tracking requirements, and feature sets without code duplication or rigid hierarchies.

## Features

- **Product Type Definition**: Central catalog of product definitions with features and constraints
- **Product Instance Tracking**: Multiple tracking strategies (serial numbers, batches, or both)
- **Dynamic Feature System**: Type-safe, constraint-validated product features
- **Multiple Identifier Types**: Support for UUID, GTIN, ISBN identifiers
- **Serial Number Validation**: IMEI, VIN, and custom serial number formats
- **Batch Management**: Production batch tracking with dates
- **Type Safety**: Full PHP 8.4 type system with strict validation
- **Clean Architecture**: Clear separation of concerns with DDD principles

## Core Concepts

### The Product Model

The Product archetype distinguishes between:

1. **ProductType**: The definition or "blueprint" of a product (e.g., "iPhone 15 Pro 256GB", "Organic Milk 1L")
2. **ProductInstance**: Actual physical items of that product (e.g., iPhone with serial ABC123, milk batch LOT-2025-001)

This separation enables:
- Catalog management independent of inventory
- Flexible feature definitions that instances must satisfy
- Different tracking strategies based on product characteristics

### Domain Model

```
┌─────────────────────────────────────────────────────────────┐
│                       ProductType                            │
│  (Aggregate Root - Product Definition)                      │
├─────────────────────────────────────────────────────────────┤
│ - ProductIdentifier (UUID/GTIN/ISBN)                        │
│ - ProductName                                               │
│ - ProductDescription                                        │
│ - Unit (preferred unit of measure)                          │
│ - ProductTrackingStrategy                                   │
│ - ProductFeatureTypes (with constraints)                    │
└─────────────────────────────────────────────────────────────┘
                           │
                           │ describes
                           ▼
┌─────────────────────────────────────────────────────────────┐
│                     ProductInstance                          │
│  (Aggregate Root - Actual Product Item)                     │
├─────────────────────────────────────────────────────────────┤
│ - ProductInstanceId                                         │
│ - ProductType reference                                     │
│ - SerialNumber (optional, based on strategy)               │
│ - BatchId (optional, based on strategy)                    │
│ - Quantity                                                  │
│ - ProductFeatureInstances (actual values)                  │
└─────────────────────────────────────────────────────────────┘
```

### Tracking Strategies

**ProductTrackingStrategy** determines how individual items are distinguished:

| Strategy | Description | Use Case |
|----------|-------------|----------|
| `UNIQUE` | One-of-a-kind items | Original artwork, prototypes |
| `INDIVIDUALLY_TRACKED` | Each item has a serial number | Electronics, appliances |
| `BATCH_TRACKED` | Grouped by production batch | Pharmaceuticals, food products |
| `INDIVIDUALLY_AND_BATCH_TRACKED` | Both serial and batch | Medical devices, regulated products |
| `IDENTICAL` | Interchangeable items | Commodity products, bulk materials |

### Feature System

Products can have typed, constrained features:

```php
// Define a feature type with constraints
$colorFeature = ProductFeatureType::withAllowedValues(
    'color',
    ['Red', 'Blue', 'Green', 'Black']
);

$sizeFeature = ProductFeatureType::withAllowedValues(
    'size',
    ['S', 'M', 'L', 'XL']
);

$memoryFeature = ProductFeatureType::withNumericRange(
    'memory_gb',
    64,
    1024
);
```

**Constraint Types:**
- `AllowedValuesConstraint` - Discrete set of allowed values
- `NumericRangeConstraint` - Integer range validation
- `DecimalRangeConstraint` - BigDecimal range validation
- `RegexConstraint` - Pattern matching for text
- `DateRangeConstraint` - Temporal boundaries
- `UnconstrainedConstraint` - Any value of specified type

### Product Identifiers

Multiple identifier formats supported:

- **UuidProductIdentifier**: Universal unique identifier
- **GtinProductIdentifier**: GTIN-8, GTIN-12, GTIN-13, GTIN-14 barcodes
- **IsbnProductIdentifier**: ISBN-10 and ISBN-13 for books

### Serial Numbers

Validated serial number formats:

- **TextualSerialNumber**: Generic alphanumeric serial
- **ImeiSerialNumber**: 15-digit mobile device identifiers
- **VinSerialNumber**: 17-character vehicle identification

## Usage Examples

### Defining Product Types

```php
use SoftwareArchetypes\Product\ProductType;
use SoftwareArchetypes\Product\ProductName;
use SoftwareArchetypes\Product\ProductDescription;
use SoftwareArchetypes\Product\Unit;
use SoftwareArchetypes\Product\Identifier\UuidProductIdentifier;
use SoftwareArchetypes\Product\Feature\ProductFeatureType;

// Define a smartphone product type
$smartphone = ProductType::individuallyTracked(
    UuidProductIdentifier::generate(),
    ProductName::of('iPhone 15 Pro'),
    ProductDescription::of('Latest flagship smartphone with A17 Pro chip'),
    Unit::piece()
);

// Define a pharmaceutical product with batch tracking
$medicine = ProductType::batchTracked(
    UuidProductIdentifier::generate(),
    ProductName::of('Aspirin 500mg'),
    ProductDescription::of('Pain relief medication'),
    Unit::piece()
);

// Define a product with features using builder
$tshirt = ProductType::builder()
    ->withId(UuidProductIdentifier::generate())
    ->withName(ProductName::of('Cotton T-Shirt'))
    ->withDescription(ProductDescription::of('100% organic cotton'))
    ->withPreferredUnit(Unit::piece())
    ->withTrackingStrategy(ProductTrackingStrategy::IDENTICAL)
    ->withFeatureType(ProductFeatureType::withAllowedValues('color', ['Red', 'Blue', 'Black']))
    ->withFeatureType(ProductFeatureType::withAllowedValues('size', ['S', 'M', 'L', 'XL']))
    ->build();
```

### Creating Product Instances

```php
use SoftwareArchetypes\Product\ProductInstance;
use SoftwareArchetypes\Product\ProductInstanceId;
use SoftwareArchetypes\Product\SerialNumber\TextualSerialNumber;
use SoftwareArchetypes\Product\Batch\Batch;
use SoftwareArchetypes\Product\Batch\BatchId;
use SoftwareArchetypes\Product\Batch\BatchName;
use SoftwareArchetypes\Product\Feature\ProductFeatureInstance;
use SoftwareArchetypes\Product\Feature\ProductFeatureInstances;

// Create a specific smartphone instance
$phone = ProductInstance::builder()
    ->withId(ProductInstanceId::generate())
    ->withProductType($smartphone)
    ->withSerialNumber(TextualSerialNumber::of('IMEI-123456789012345'))
    ->withQuantity(1)
    ->build();

// Create a medicine batch
$batch = Batch::of(
    BatchId::generate(),
    BatchName::of('LOT-2025-001'),
    new DateTimeImmutable('2025-01-15'),
    new DateTimeImmutable('2027-01-15')
);

$medicineBatch = ProductInstance::builder()
    ->withId(ProductInstanceId::generate())
    ->withProductType($medicine)
    ->withBatchId($batch->id())
    ->withQuantity(1000)
    ->build();

// Create t-shirt with specific features
$colorFeature = ProductFeatureInstance::of(
    $tshirt->featureTypes()->get('color'),
    'Blue'
);

$sizeFeature = ProductFeatureInstance::of(
    $tshirt->featureTypes()->get('size'),
    'L'
);

$blueTshirtLarge = ProductInstance::builder()
    ->withId(ProductInstanceId::generate())
    ->withProductType($tshirt)
    ->withFeatures(new ProductFeatureInstances([
        $colorFeature,
        $sizeFeature
    ]))
    ->build();
```

### Using Product Identifiers

```php
use SoftwareArchetypes\Product\Identifier\GtinProductIdentifier;
use SoftwareArchetypes\Product\Identifier\IsbnProductIdentifier;

// GTIN barcode for retail products
$gtin = GtinProductIdentifier::of('5901234123457'); // GTIN-13

// ISBN for books
$isbn = IsbnProductIdentifier::of('978-0-13-468599-1'); // ISBN-13
```

### Working with Features and Constraints

```php
// Create feature types with different constraints
$priceFeature = ProductFeatureType::withDecimalRange(
    'price',
    BigDecimal::of('0.01'),
    BigDecimal::of('9999.99')
);

$warrantyFeature = ProductFeatureType::withDateRange(
    'warranty_until',
    new DateTimeImmutable('2025-01-01'),
    new DateTimeImmutable('2030-12-31')
);

$skuFeature = ProductFeatureType::withRegex(
    'sku',
    '/^[A-Z]{3}-\d{4}$/' // Format: ABC-1234
);

// Create instances with validated values
$priceInstance = ProductFeatureInstance::of($priceFeature, BigDecimal::of('499.99'));
$warrantyInstance = ProductFeatureInstance::of($warrantyFeature, new DateTimeImmutable('2027-12-31'));
$skuInstance = ProductFeatureInstance::of($skuFeature, 'IPH-1015');
```

### Application Facade

```php
use SoftwareArchetypes\Product\Application\ProductFacade;
use SoftwareArchetypes\Product\Infrastructure\InMemoryProductTypeRepository;

$facade = new ProductFacade(new InMemoryProductTypeRepository());

// Define and store product type
$facade->defineProductType($smartphone);

// Retrieve product type
$retrieved = $facade->getProductType($smartphone->id());

// Check existence
if ($facade->productTypeExists($smartphone->id())) {
    echo "Product type exists in catalog\n";
}

// Get all product types
$allProducts = $facade->getAllProductTypes();
```

## Business Rules

1. **Tracking Validation**: Product instances must have appropriate tracking identifiers based on their product type's strategy
2. **Feature Validation**: Feature instances must satisfy constraints defined in feature types
3. **Identifier Validation**: Product identifiers must conform to their format specifications (GTIN length, ISBN checksum, etc.)
4. **Serial Number Validation**: Serial numbers must match format requirements (IMEI 15 digits, VIN 17 characters)
5. **Immutability**: All value objects are immutable; changes require creating new instances
6. **Type Safety**: Strong typing prevents invalid assignments and operations

## Architecture

```
src/
├── ProductType.php                      # Aggregate root - product definition
├── ProductInstance.php                  # Aggregate root - product item
├── ProductTypeBuilder.php               # Builder for ProductType
├── ProductInstanceBuilder.php           # Builder for ProductInstance
├── ProductInstanceId.php                # Value object
├── ProductName.php                      # Value object
├── ProductDescription.php               # Value object
├── ProductTrackingStrategy.php          # Enum
├── FeatureValueType.php                 # Enum
├── Unit.php                             # Value object
├── Validity.php                         # Value object for date ranges
├── ProductTypeRepository.php            # Repository interface
│
├── Feature/                             # Feature system
│   ├── ProductFeatureType.php          # Feature definition with constraints
│   ├── ProductFeatureInstance.php      # Actual feature value
│   ├── ProductFeatureTypes.php         # Collection wrapper
│   └── ProductFeatureInstances.php     # Collection wrapper
│
├── Constraint/                          # Constraint system
│   ├── FeatureValueConstraint.php      # Interface
│   ├── UnconstrainedConstraint.php     # No validation
│   ├── AllowedValuesConstraint.php     # Discrete values
│   ├── NumericRangeConstraint.php      # Integer range
│   ├── DecimalRangeConstraint.php      # Decimal range
│   ├── RegexConstraint.php             # Pattern matching
│   └── DateRangeConstraint.php         # Date range
│
├── Identifier/                          # Product identifiers
│   ├── ProductIdentifier.php           # Abstract base
│   ├── UuidProductIdentifier.php       # UUID format
│   ├── GtinProductIdentifier.php       # GTIN barcodes
│   └── IsbnProductIdentifier.php       # ISBN for books
│
├── SerialNumber/                        # Serial number types
│   ├── SerialNumber.php                # Abstract base
│   ├── TextualSerialNumber.php         # Generic format
│   ├── ImeiSerialNumber.php            # Mobile devices
│   └── VinSerialNumber.php             # Vehicles
│
├── Batch/                               # Batch tracking
│   ├── Batch.php                       # Batch aggregate
│   ├── BatchId.php                     # Value object
│   └── BatchName.php                   # Value object
│
├── Application/                         # Application layer
│   └── ProductFacade.php               # Main API facade
│
└── Infrastructure/                      # Infrastructure layer
    └── InMemoryProductTypeRepository.php
```

### Layer Dependencies

Validated with **Deptrac**:
- **Core**: No dependencies on other layers
- **Application**: Depends on Core, Infrastructure
- **Infrastructure**: Depends on Core

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

# Code style check
composer phpcs

# Code style fix
composer phpcs-fix

# Run full CI pipeline
composer ci
```

## Test Statistics

- **Total Tests**: 230
- **Total Assertions**: 489
- **Unit Tests**: 220
- **Integration Tests**: 10
- **Code Coverage**: Comprehensive
- **Status**: ✓ All passing

## Use Cases

The Product archetype is ideal for:

- **E-commerce Platforms**: Product catalog management with variants
- **Inventory Systems**: Tracking products with serial numbers and batches
- **Manufacturing**: Managing products with production batches
- **Retail**: POS systems with GTIN barcodes
- **Regulated Industries**: Products requiring full traceability (pharmaceuticals, medical devices)
- **Electronics**: Serial number tracking for warranty and support
- **Automotive**: VIN-based vehicle tracking
- **Publishing**: ISBN-based book management

## Key Design Patterns

### Aggregate Pattern
- **ProductType** and **ProductInstance** are aggregate roots managing their own consistency
- Features and constraints are managed internally
- All changes go through the aggregate roots

### Value Objects
- Immutable: **ProductIdentifier**, **SerialNumber**, **BatchId**, **ProductName**, **ProductDescription**, **Unit**
- Encapsulate validation and business logic
- Enable type safety and prevent primitive obsession

### Builder Pattern
- Fluent API for constructing complex aggregates
- Step-by-step validation
- Clear, readable construction code

### Strategy Pattern
- **ProductTrackingStrategy** determines validation rules
- **FeatureValueConstraint** provides pluggable validation strategies
- Extensible without modifying core classes

### Repository Pattern
- **ProductTypeRepository** abstracts persistence
- Clean separation between domain and infrastructure

### Facade Pattern
- **ProductFacade** provides simplified application-level API
- Hides complexity of aggregate construction and management

## PHP 8.4 Features Used

- **Constructor property promotion**: Concise class definitions
- **Readonly properties**: Immutable value objects
- **Enums**: Type-safe tracking strategies and value types
- **Named arguments**: Clear method calls
- **Union types**: Flexible type declarations
- **Match expressions**: Cleaner conditional logic

## Differences from Java Implementation

1. **No sealed classes**: PHP uses abstract classes instead
2. **Array collections**: PHP uses arrays instead of Java's List/Set
3. **Builder pattern**: Separate builder classes per PSR-12
4. **Nullable types**: PHP uses `?Type` instead of Java's `Optional<T>`
5. **BigDecimal**: Using brick/math library for precise decimal calculations

## Tech Stack

- **PHP**: 8.4+
- **PHPUnit**: 11.x (testing)
- **PHPStan**: Level max (static analysis)
- **Deptrac**: 3.x (architecture validation)
- **Brick Math**: 0.12+ (arbitrary precision mathematics)
- **Ramsey UUID**: 4.x (UUID generation)

## Development

### Requirements

- PHP 8.4 or higher
- Composer 2.x

### Install Dependencies

```bash
cd product
composer install
```

### Run Quality Checks

```bash
# Run all checks
composer ci
```

## Advanced Capabilities

### Product Variants

Model product variants (e.g., sizes, colors) using features:

```php
$baseShirt = ProductType::builder()
    ->withFeatureType(ProductFeatureType::withAllowedValues('color', ['Red', 'Blue']))
    ->withFeatureType(ProductFeatureType::withAllowedValues('size', ['S', 'M', 'L']))
    ->build();

// Create variant instances
$redSmall = /* ProductInstance with color=Red, size=S */
$blueLarge = /* ProductInstance with color=Blue, size=L */
```

### Complex Constraints

Combine multiple constraints for sophisticated validation:

```php
$productCode = ProductFeatureType::withRegex(
    'product_code',
    '/^[A-Z]{2}\d{4}-[A-Z]{3}$/'
);

$price = ProductFeatureType::withDecimalRange(
    'price',
    BigDecimal::of('1.00'),
    BigDecimal::of('999999.99')
);
```

### Batch Genealogy

Track product lineage through batch relationships:

```php
$batch1 = Batch::of(/* initial production */);
$batch2 = Batch::of(/* derived from batch1 */);

// Link instances to their production batches
$instance1 = ProductInstance::builder()->withBatchId($batch1->id())->build();
$instance2 = ProductInstance::builder()->withBatchId($batch2->id())->build();
```

## Credits

Ported from the [Software Archetypes](https://github.com/Software-Archetypes/archetypes) product module originally implemented in Java.

Original authors: Software Archetypes community

## License

MIT License

## Related Patterns

- **Quantity**: Value objects for measurements and units
- **Pricing**: Calculating prices for products
- **Party**: Customers and suppliers related to products
- **Accounting**: Financial transactions for product sales

## References

For more information on archetype patterns:
- [Software Archetypes GitHub](https://github.com/Software-Archetypes/archetypes)
- [Enterprise Patterns and MDA](https://www.amazon.com/Enterprise-Patterns-MDA-Building-Archetype/dp/032111230X)

**Sources:**
- [Model-Driven Development with UML and Modern Java](https://www.archetypesoftware.com/blog/rebel-bitbucket)
- [Enterprise Patterns and MDA: Building Better Software with Archetype Patterns and UML](https://www.oreilly.com/library/view/enterprise-patterns-and/032111230X/)
