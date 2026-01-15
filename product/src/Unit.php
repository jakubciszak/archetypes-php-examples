<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product;

/**
 * Represents a unit of measurement for products.
 * This is a simple value object wrapping a string representing the unit.
 */
final readonly class Unit
{
    private function __construct(
        private string $value
    ) {
    }

    public static function of(string $value): self
    {
        return new self($value);
    }

    public static function piece(): self
    {
        return new self('piece');
    }

    public static function kilogram(): self
    {
        return new self('kilogram');
    }

    public static function gram(): self
    {
        return new self('gram');
    }

    public static function liter(): self
    {
        return new self('liter');
    }

    public static function milliliter(): self
    {
        return new self('milliliter');
    }

    public static function meter(): self
    {
        return new self('meter');
    }

    public static function centimeter(): self
    {
        return new self('centimeter');
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
