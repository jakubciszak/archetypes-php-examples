<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product;

use InvalidArgumentException;

/**
 * Name of a product type.
 * Must be non-empty and serves as the primary textual identifier.
 */
final readonly class ProductName
{
    private string $value;

    private function __construct(string $value)
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            throw new InvalidArgumentException('Product name cannot be empty');
        }
        $this->value = $trimmed;
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
