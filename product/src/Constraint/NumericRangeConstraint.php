<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Constraint;

use InvalidArgumentException;
use SoftwareArchetypes\Product\FeatureValueType;

/**
 * Constraint that restricts integer values to a numeric range.
 * Validates that values fall between minimum and maximum bounds (inclusive).
 */
final readonly class NumericRangeConstraint implements FeatureValueConstraint
{
    public function __construct(
        private int $minimum,
        private int $maximum
    ) {
        if ($this->minimum > $this->maximum) {
            throw new InvalidArgumentException('Minimum value cannot be greater than maximum value');
        }
    }

    public function isSatisfiedBy(mixed $value): bool
    {
        if (!is_int($value)) {
            return false;
        }

        return $value >= $this->minimum && $value <= $this->maximum;
    }

    public function valueType(): FeatureValueType
    {
        return FeatureValueType::INTEGER;
    }

    public function minimum(): int
    {
        return $this->minimum;
    }

    public function maximum(): int
    {
        return $this->maximum;
    }
}
