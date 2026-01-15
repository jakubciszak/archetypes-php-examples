<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Constraint;

use DateTimeImmutable;
use InvalidArgumentException;
use SoftwareArchetypes\Product\FeatureValueType;

/**
 * Constraint that restricts date values to a date range.
 * Validates that dates fall between minimum and maximum bounds (inclusive).
 * Comparison is based on calendar dates only, ignoring time components.
 */
final readonly class DateRangeConstraint implements FeatureValueConstraint
{
    public function __construct(
        private DateTimeImmutable $minimum,
        private DateTimeImmutable $maximum
    ) {
        $minDate = $this->minimum->setTime(0, 0, 0);
        $maxDate = $this->maximum->setTime(0, 0, 0);

        if ($minDate > $maxDate) {
            throw new InvalidArgumentException('Minimum date cannot be after maximum date');
        }
    }

    public function isSatisfiedBy(mixed $value): bool
    {
        if (!$value instanceof DateTimeImmutable) {
            return false;
        }

        // Compare only the date portion, ignoring time
        $valueDate = $value->setTime(0, 0, 0);
        $minDate = $this->minimum->setTime(0, 0, 0);
        $maxDate = $this->maximum->setTime(0, 0, 0);

        return $valueDate >= $minDate && $valueDate <= $maxDate;
    }

    public function valueType(): FeatureValueType
    {
        return FeatureValueType::DATE;
    }

    public function minimum(): DateTimeImmutable
    {
        return $this->minimum;
    }

    public function maximum(): DateTimeImmutable
    {
        return $this->maximum;
    }
}
