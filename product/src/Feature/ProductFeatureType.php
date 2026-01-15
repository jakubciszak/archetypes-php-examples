<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Feature;

use InvalidArgumentException;
use SoftwareArchetypes\Product\Constraint\FeatureValueConstraint;
use SoftwareArchetypes\Product\FeatureValueType;

/**
 * Defines a type of feature that can be associated with products.
 * Features describe characteristics or attributes of products (e.g., Color, Size, Weight).
 * Each feature type has a name and a constraint that defines valid values.
 */
final readonly class ProductFeatureType
{
    private string $name;

    public function __construct(
        string $name,
        private FeatureValueConstraint $constraint
    ) {
        $trimmedName = trim($name);
        if ($trimmedName === '') {
            throw new InvalidArgumentException('Feature type name cannot be empty');
        }
        $this->name = $trimmedName;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function constraint(): FeatureValueConstraint
    {
        return $this->constraint;
    }

    public function valueType(): FeatureValueType
    {
        return $this->constraint->valueType();
    }

    public function isValidValue(mixed $value): bool
    {
        return $this->constraint->isSatisfiedBy($value);
    }
}
