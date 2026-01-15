<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Constraint;

use SoftwareArchetypes\Product\FeatureValueType;

/**
 * Constraint that accepts any value of the specified type.
 * No additional restrictions beyond type checking.
 */
final readonly class UnconstrainedConstraint implements FeatureValueConstraint
{
    public function __construct(
        private FeatureValueType $valueType
    ) {
    }

    public function isSatisfiedBy(mixed $value): bool
    {
        return $this->valueType->isInstance($value);
    }

    public function valueType(): FeatureValueType
    {
        return $this->valueType;
    }
}
