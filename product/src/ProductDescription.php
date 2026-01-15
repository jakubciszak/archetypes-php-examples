<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product;

/**
 * Description of a product type.
 * Provides detailed information about the product's features, use cases, etc.
 * Can be empty.
 */
final readonly class ProductDescription
{
    private string $value;

    private function __construct(string $value)
    {
        $this->value = trim($value);
    }

    public static function of(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function asString(): string
    {
        return $this->value;
    }
}
