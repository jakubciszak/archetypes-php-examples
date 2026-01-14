<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Quantity;

use InvalidArgumentException;

/**
 * Unit of measurement for quantities.
 * Examples: kg, l, pcs, m3, m2, hours, etc.
 */
final readonly class Unit
{
    private function __construct(
        private string $symbol,
        private string $name
    ) {
        if (trim($this->symbol) === '') {
            throw new InvalidArgumentException('Unit symbol cannot be null or blank');
        }

        if (trim($this->name) === '') {
            throw new InvalidArgumentException('Unit name cannot be null or blank');
        }
    }

    public static function of(string $symbol, string $name): self
    {
        return new self($symbol, $name);
    }

    public static function pieces(): self
    {
        return new self('pcs', 'pieces');
    }

    public static function kilograms(): self
    {
        return new self('kg', 'kilograms');
    }

    public static function liters(): self
    {
        return new self('l', 'liters');
    }

    public static function meters(): self
    {
        return new self('m', 'meters');
    }

    public static function squareMeters(): self
    {
        return new self('m²', 'square meters');
    }

    public static function cubicMeters(): self
    {
        return new self('m³', 'cubic meters');
    }

    public static function hours(): self
    {
        return new self('h', 'hours');
    }

    public static function minutes(): self
    {
        return new self('min', 'minutes');
    }

    public function symbol(): string
    {
        return $this->symbol;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function equals(Unit $other): bool
    {
        return $this->symbol === $other->symbol
            && $this->name === $other->name;
    }

    public function __toString(): string
    {
        return $this->symbol;
    }
}
