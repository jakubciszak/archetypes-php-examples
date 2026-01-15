<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Constraint;

use SoftwareArchetypes\Product\FeatureValueType;

/**
 * Defines constraints that limit acceptable values for product features.
 * Constraints validate that feature values meet specific criteria.
 */
interface FeatureValueConstraint
{
    /**
     * Checks if a value satisfies this constraint.
     */
    public function isSatisfiedBy(mixed $value): bool;

    /**
     * Returns the value type this constraint applies to.
     */
    public function valueType(): FeatureValueType;
}
