<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Constraint;

use InvalidArgumentException;
use SoftwareArchetypes\Product\FeatureValueType;

/**
 * Constraint that restricts values to a specific list of allowed values.
 * Commonly used for enumerated choices like sizes, colors, categories.
 */
final readonly class AllowedValuesConstraint implements FeatureValueConstraint
{
    /**
     * @param array<mixed> $allowedValues
     */
    public function __construct(
        private FeatureValueType $valueType,
        private array $allowedValues
    ) {
        if (empty($this->allowedValues)) {
            throw new InvalidArgumentException('Allowed values list cannot be empty');
        }
    }

    public function isSatisfiedBy(mixed $value): bool
    {
        if (!$this->valueType->isInstance($value)) {
            return false;
        }

        return in_array($value, $this->allowedValues, true);
    }

    public function valueType(): FeatureValueType
    {
        return $this->valueType;
    }

    /**
     * @return array<mixed>
     */
    public function allowedValues(): array
    {
        return $this->allowedValues;
    }
}
