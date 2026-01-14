<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Quantity;

use Brick\Math\BigDecimal;
use Brick\Math\Exception\MathException;
use InvalidArgumentException;

/**
 * Quantity represents an amount with a unit of measurement.
 * Examples: 100 kg, 500 liters, 1000 pieces, 25.5 m²
 */
final readonly class Quantity
{
    private BigDecimal $amount;

    private function __construct(
        BigDecimal|int|float|string $amount,
        private Unit $unit
    ) {
        try {
            $this->amount = $amount instanceof BigDecimal ? $amount : BigDecimal::of($amount);
        } catch (MathException $e) {
            throw new InvalidArgumentException('Invalid amount: ' . $e->getMessage(), 0, $e);
        }

        if ($this->amount->isNegative()) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }
    }

    public static function of(BigDecimal|int|float|string $amount, Unit $unit): self
    {
        return new self($amount, $unit);
    }

    public function amount(): BigDecimal
    {
        return $this->amount;
    }

    public function unit(): Unit
    {
        return $this->unit;
    }

    public function add(Quantity $other): self
    {
        if (!$this->unit->equals($other->unit)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Cannot add quantities with different units: %s and %s',
                    $this->unit,
                    $other->unit
                )
            );
        }

        return new self($this->amount->plus($other->amount), $this->unit);
    }

    public function subtract(Quantity $other): self
    {
        if (!$this->unit->equals($other->unit)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Cannot subtract quantities with different units: %s and %s',
                    $this->unit,
                    $other->unit
                )
            );
        }

        return new self($this->amount->minus($other->amount), $this->unit);
    }

    public function isZero(): bool
    {
        return $this->amount->isZero();
    }

    public function isGreaterThan(Quantity $other): bool
    {
        if (!$this->unit->equals($other->unit)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Cannot compare quantities with different units: %s and %s',
                    $this->unit,
                    $other->unit
                )
            );
        }

        return $this->amount->isGreaterThan($other->amount);
    }

    public function isLessThan(Quantity $other): bool
    {
        if (!$this->unit->equals($other->unit)) {
            throw new InvalidArgumentException(
                sprintf(
                    'Cannot compare quantities with different units: %s and %s',
                    $this->unit,
                    $other->unit
                )
            );
        }

        return $this->amount->isLessThan($other->amount);
    }

    public function equals(Quantity $other): bool
    {
        return $this->amount->isEqualTo($other->amount)
            && $this->unit->equals($other->unit);
    }

    public function __toString(): string
    {
        return $this->amount->stripTrailingZeros() . ' ' . $this->unit;
    }
}
