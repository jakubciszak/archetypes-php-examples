# Party Archetype - PHP Implementation

A PHP 8.4 implementation of the Party archetype pattern from the [Software Archetypes](https://github.com/Software-Archetypes/archetypes) project.

## Overview

The Party archetype provides a flexible model for representing entities (individuals and organizations) that can assume multiple roles dynamically. This pattern solves the problem of managing diverse entity types that can simultaneously be customers, suppliers, partners, or users without data duplication or rigid structures.

## Features

- **Universal Entity Representation**: Single model for persons and organizations
- **Dynamic Role Management**: Entities can have multiple roles that can be added/removed dynamically
- **Registered Identifiers**: Support for domain-specific identifiers (tax numbers, passports, etc.)
- **Event-Driven Architecture**: Domain events for all state changes
- **Type Safety**: Full PHP 8.4 type system with generics via PHPDoc
- **Result Monad**: Functional error handling for explicit success/failure states

## Core Concepts

### The Party Model

The center of the model is **not a role**, but an **entity** such as a person or an organization. This paradigm shift enables:

- Universal representation across all entity types within a single model
- Dynamic role assignment allowing a single Party to function as customer, supplier, or partner simultaneously
- Unified data management eliminating redundancy across applications

### Domain Model

#### Basic Model

![Party Basic Model](diagrams/party-basic-model.png)

The basic model consists of:

**Party** (Abstract) - Base aggregate root with:
- `PartyId` - Universal unique identifier
- `Role[]` - Dynamic collection of roles
- `RegisteredIdentifier[]` - Domain-specific identifiers
- `Version` - Optimistic locking support

**Person** - Concrete implementation for individuals:
- `PersonalData` - First name, last name
- Inherits role and identifier management from Party

**Company** - Concrete implementation for organizations:
- `OrganizationName` - Company name
- Inherits role and identifier management from Party

#### Extended Model with Data and Authentication

![Party with Data and Auth](diagrams/party-with-data-and-auth.png)

The extended model can include:
- Address management (geographic, email, web, telecom)
- Authentication data
- Contact information
- Additional domain-specific attributes

### Value Objects

- **`PartyId`** - System-wide unique identifier transcending entity types
- **`Role`** - Represents functions like Customer, Supplier, Partner, Employee
- **`RegisteredIdentifier`** - Domain-specific identifiers (tax numbers, passport numbers, etc.)
- **`PersonalData`** - First name and last name for persons
- **`OrganizationName`** - Name for companies
- **`Version`** - Optimistic locking version

### Domain Events

**Registration Events:**
- `PersonRegistered` - Published when a person is registered
- `CompanyRegistered` - Published when a company is registered

**Role Management Events:**
- `RoleAdded` - Role successfully added to party
- `RoleAdditionSkipped` - Role already assigned
- `RoleRemoved` - Role successfully removed
- `RoleRemovalSkipped` - Role was not assigned

**Identifier Management Events:**
- `RegisteredIdentifierAdded` - Identifier successfully added
- `RegisteredIdentifierAdditionSkipped` - Identifier already exists
- `RegisteredIdentifierRemoved` - Identifier successfully removed
- `RegisteredIdentifierRemovalSkipped` - Identifier not found

**Update Events:**
- `PersonalDataUpdated` - Personal data changed
- `PersonalDataUpdateSkipped` - No changes detected
- `OrganizationNameUpdated` - Organization name changed
- `OrganizationNameUpdateSkipped` - No changes detected

## Usage Examples

### Registering Parties

```php
use SoftwareArchetypes\Party\Person;
use SoftwareArchetypes\Party\Company;
use SoftwareArchetypes\Party\PartyId;
use SoftwareArchetypes\Party\PersonalData;
use SoftwareArchetypes\Party\OrganizationName;
use SoftwareArchetypes\Party\Role;
use SoftwareArchetypes\Party\Common\Version;

// Register a person
$personId = PartyId::newOne();
$person = new Person(
    $personId,
    new PersonalData('John', 'Doe'),
    [],  // roles
    [],  // identifiers
    Version::zero()
);

// Register a company
$companyId = PartyId::newOne();
$company = new Company(
    $companyId,
    OrganizationName::of('Acme Corporation'),
    [],  // roles
    [],  // identifiers
    Version::zero()
);
```

### Managing Roles

```php
// Add roles to a party
$person->addRole(Role::of('Customer'));
$person->addRole(Role::of('Supplier'));

// A party can have multiple roles simultaneously
foreach ($person->roles() as $role) {
    echo "Role: " . $role->asString() . "\n";
}

// Remove a role
$person->removeRole(Role::of('Supplier'));

// Events are tracked
foreach ($person->events() as $event) {
    echo $event->getType() . "\n";
}
```

### Managing Registered Identifiers

```php
use SoftwareArchetypes\Party\RegisteredIdentifier;

// Add tax number
$taxId = RegisteredIdentifier::of('TAX-123456789');
$person->addIdentifier($taxId);

// Add passport number
$passport = RegisteredIdentifier::of('PASSPORT-AB123456');
$person->addIdentifier($passport);

// List all identifiers
foreach ($person->registeredIdentifiers() as $identifier) {
    echo $identifier->asString() . "\n";
}

// Remove an identifier
$person->removeIdentifier($taxId);
```

### Updating Party Data

```php
// Update person's personal data
$newData = new PersonalData('Jane', 'Smith');
$result = $person->update($newData);

if ($result->isSuccess()) {
    echo "Personal data updated\n";
}

// Update company name
$newName = OrganizationName::of('Acme Industries Inc.');
$result = $company->update($newName);
```

### Working with Result Monad

```php
// The Result monad provides functional error handling
$result = $person->addRole(Role::of('Partner'));

// Map success value
$mapped = $result->map(fn($party) => $party->id());

// Fold to single value
$message = $result->fold(
    fn($party) => "Success: Party has " . count($party->roles()) . " roles",
    fn($event) => "Skipped: " . $event->getReason()
);

// Side effects
$result->peekSuccess(fn($party) => $logger->info("Role added"))
       ->peekFailure(fn($event) => $logger->warning("Role addition skipped"));
```

## Business Rules

1. **Role Uniqueness**: A party cannot have the same role multiple times
2. **Identifier Uniqueness**: A party cannot have duplicate registered identifiers
3. **Idempotency**: Adding existing role/identifier or removing non-existent role/identifier publishes skip events instead of failing
4. **Event Tracking**: All operations produce domain events for audit and integration
5. **Optimistic Locking**: Version field supports concurrent modification detection

## Architecture

```
src/
├── Party.php                    # Abstract aggregate root
├── Person.php                   # Concrete party type
├── Company.php                  # Concrete party type
├── PartyId.php                  # Value object
├── Role.php                     # Value object
├── RegisteredIdentifier.php     # Value object
├── PersonalData.php             # Value object
├── OrganizationName.php         # Value object
├── PartyRepository.php          # Repository interface
├── Common/                      # Shared utilities
│   ├── Result.php              # Result monad
│   └── Version.php             # Optimistic locking
├── Events/                      # Domain events
│   ├── PartyRelatedEvent.php
│   ├── PersonRegistered.php
│   ├── CompanyRegistered.php
│   ├── RoleAdded.php
│   ├── RoleRemoved.php
│   └── ...
├── Infrastructure/              # Infrastructure implementations
│   ├── InMemoryPartyRepository.php
│   └── InMemoryDomainEventsPublisher.php
└── Application/                 # Application services
    └── PartyFacade.php
```

### Layer Dependencies

Validated with **Deptrac**:
- **Common**: No dependencies
- **Core**: Depends on Common, Events
- **Events**: Depends on Common (for event metadata)
- **Application**: Depends on Core, Events, Common
- **Infrastructure**: Depends on Application, Core, Events, Common

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
composer cs-check

# Code style fix
composer cs-fix

# Run full CI pipeline
composer ci
```

## Party Relationship Pattern

For managing relationships between parties (e.g., manager-subordinate, supplier-customer), a separate **Party Relationship** aggregate can be used:

```php
PartyRelationship(
    id: RelationshipId,
    from: PartyId with Role,
    to: PartyId with Role,
    relationshipType: RelationshipType,
    validFrom: DateTimeImmutable,
    validTo: ?DateTimeImmutable
)
```

This separation:
- Prevents Party aggregate bloat
- Enables graph-based queries
- Supports temporal constraints
- Allows for eventual consistency in complex relationship networks

## Key Design Patterns

### Aggregate Pattern
- **Party** is the aggregate root managing its own consistency
- Roles and identifiers are managed internally
- All changes go through the aggregate root

### Value Objects
- Immutable: **PartyId**, **Role**, **RegisteredIdentifier**, **PersonalData**, **OrganizationName**
- Encapsulate validation and business logic
- Enable type safety

### Domain Events
- Published for all state changes
- Enable event-driven architecture
- Support audit logging and integration

### Repository Pattern
- **PartyRepository** abstracts persistence
- Supports optimistic locking with version checking

### Result Monad
- Functional error handling
- Explicit success/failure states
- Composable operations

## PHP 8.4 Features Used

- **Constructor property promotion**: Concise class definitions
- **Readonly properties**: Immutable value objects
- **Named arguments**: Clear method calls
- **Array generics via PHPDoc**: Type-safe collections
- **Abstract methods**: Polymorphic behavior

## Differences from Java Implementation

1. **No sealed classes**: PHP doesn't have sealed classes, using abstract classes instead
2. **Array collections**: PHP uses arrays instead of Java's List/Set
3. **Nullable types**: PHP uses `?Type` instead of Java's `Optional<T>`
4. **Closure syntax**: PHP closures use `fn()` and `function()` instead of Java lambdas
5. **Version object**: PHP implementation uses custom Version value object

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
cd party
composer install
```

### Run Quality Checks

```bash
# Run all checks
composer ci
```

## Use Cases

The Party archetype is ideal for:

- **CRM Systems**: Managing customers, contacts, and business relationships
- **ERP Systems**: Handling suppliers, partners, employees, and customers
- **E-commerce**: Managing users who can be both customers and sellers
- **Healthcare**: Patients, doctors, clinics, insurance companies with multiple roles
- **Financial Systems**: Account holders, beneficiaries, organizations
- **Supply Chain**: Entities playing multiple roles across the supply chain

## Advanced Capabilities

### Graph Queries

The Party model enables sophisticated queries:
- "Find all employees of suppliers operating in Warsaw"
- "List all customers who are also suppliers"
- "Show all parties with both Customer and Partner roles"

### Scalability Considerations

- **Separation of concerns**: Addresses and Relationships as independent aggregates
- **Event sourcing ready**: All state changes produce events
- **Database flexibility**: Works with relational, document, and graph databases
- **Microservices friendly**: Clear aggregate boundaries

## Credits

Ported from the [Software Archetypes](https://github.com/Software-Archetypes/archetypes) party module originally implemented in Java.

Original authors: Bartłomiej Słota and Jakub Pilimon

## License

MIT License

## Related Patterns

- **Party Relationship**: Managing relationships between parties
- **Address**: Contact information management
- **Account**: Financial accounts linked to parties
- **User**: Authentication and authorization linked to parties
