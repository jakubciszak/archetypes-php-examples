<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Constraint;

use Brick\Math\BigDecimal;
use InvalidArgumentException;
use SoftwareArchetypes\Product\FeatureValueType;

/**
 * Constraint that restricts decimal values to a numeric range.
 * Validates that values fall between minimum and maximum bounds (inclusive).
 */
final readonly class DecimalRangeConstraint implements FeatureValueConstraint
{
    public function __construct(
        private BigDecimal $minimum,
        private BigDecimal $maximum
    ) {
        if ($this->minimum->isGreaterThan($this->maximum)) {
            throw new InvalidArgumentException('Minimum value cannot be greater than maximum value');
        }
    }

    public function isSatisfiedBy(mixed $value): bool
    {
        if (!$value instanceof BigDecimal) {
            return false;
        }

        return $value->isGreaterThanOrEqualTo($this->minimum)
            && $value->isLessThanOrEqualTo($this->maximum);
    }

    public function valueType(): FeatureValueType
    {
        return FeatureValueType::DECIMAL;
    }

    public function minimum(): BigDecimal
    {
        return $this->minimum;
    }

    public function maximum(): BigDecimal
    {
        return $this->maximum;
    }
}
