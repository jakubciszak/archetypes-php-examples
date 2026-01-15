<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Feature;

use InvalidArgumentException;

/**
 * Represents an actual feature value for a product.
 * Associates a ProductFeatureType with a concrete value that satisfies its constraint.
 */
final readonly class ProductFeatureInstance
{
    public function __construct(
        private ProductFeatureType $type,
        private mixed $value
    ) {
        if (!$this->type->isValidValue($this->value)) {
            throw new InvalidArgumentException(
                'Value does not satisfy constraint for feature type: ' . $this->type->name()
            );
        }
    }

    public function type(): ProductFeatureType
    {
        return $this->type;
    }

    public function value(): mixed
    {
        return $this->value;
    }

    public function name(): string
    {
        return $this->type->name();
    }
}
