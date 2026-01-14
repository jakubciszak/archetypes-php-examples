<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Quantity\Money;

use Brick\Math\BigDecimal;
use Brick\Math\Exception\MathException;
use Brick\Math\RoundingMode;
use InvalidArgumentException;

/**
 * Money represents a monetary amount with currency.
 * This is a specialized quantity for financial calculations.
 */
final readonly class Money
{
    private BigDecimal $amount;

    private function __construct(
        BigDecimal|int|float|string $amount,
        private string $currency
    ) {
        try {
            $this->amount = $amount instanceof BigDecimal ? $amount : BigDecimal::of($amount);
        } catch (MathException $e) {
            throw new InvalidArgumentException('Invalid amount: ' . $e->getMessage(), 0, $e);
        }
    }

    public static function pln(BigDecimal|int|float|string $amount): self
    {
        return new self($amount, 'PLN');
    }

    public static function zeroPln(): self
    {
        return self::pln(0);
    }

    public static function onePln(): self
    {
        return self::pln(1);
    }

    public function value(): BigDecimal
    {
        return $this->amount;
    }

    public function currency(): string
    {
        return $this->currency;
    }

    public function add(Money $other): self
    {
        $this->assertSameCurrency($other);
        return new self($this->amount->plus($other->amount), $this->currency);
    }

    public function subtract(Money $other): self
    {
        $this->assertSameCurrency($other);
        return new self($this->amount->minus($other->amount), $this->currency);
    }

    public function multiply(BigDecimal|int|float|string $multiplier): self
    {
        try {
            $multiplierDecimal = $multiplier instanceof BigDecimal ? $multiplier : BigDecimal::of($multiplier);
        } catch (MathException $e) {
            throw new InvalidArgumentException('Invalid multiplier: ' . $e->getMessage(), 0, $e);
        }

        return new self($this->amount->multipliedBy($multiplierDecimal), $this->currency);
    }

    public function divide(BigDecimal|int|float|string $divisor): self
    {
        try {
            $divisorDecimal = $divisor instanceof BigDecimal ? $divisor : BigDecimal::of($divisor);
        } catch (MathException $e) {
            throw new InvalidArgumentException('Invalid divisor: ' . $e->getMessage(), 0, $e);
        }

        if ($divisorDecimal->isZero()) {
            throw new InvalidArgumentException('Cannot divide by zero');
        }

        // Round to 2 decimal places for currency
        return new self(
            $this->amount->dividedBy($divisorDecimal, 2, RoundingMode::HALF_UP),
            $this->currency
        );
    }

    public function negate(): self
    {
        return new self($this->amount->negated(), $this->currency);
    }

    public function abs(): self
    {
        return new self($this->amount->abs(), $this->currency);
    }

    public function isZero(): bool
    {
        return $this->amount->isZero();
    }

    public function isNegative(): bool
    {
        return $this->amount->isNegative();
    }

    public function isGreaterThan(Money $other): bool
    {
        $this->assertSameCurrency($other);
        return $this->amount->isGreaterThan($other->amount);
    }

    public function isGreaterThanOrEqualTo(Money $other): bool
    {
        $this->assertSameCurrency($other);
        return $this->amount->isGreaterThanOrEqualTo($other->amount);
    }

    public function isLessThan(Money $other): bool
    {
        $this->assertSameCurrency($other);
        return $this->amount->isLessThan($other->amount);
    }

    public static function min(Money $one, Money $two): self
    {
        $one->assertSameCurrency($two);
        return $one->amount->isLessThanOrEqualTo($two->amount) ? $one : $two;
    }

    public static function max(Money $one, Money $two): self
    {
        $one->assertSameCurrency($two);
        return $one->amount->isGreaterThanOrEqualTo($two->amount) ? $one : $two;
    }

    public function compareTo(Money $other): int
    {
        $this->assertSameCurrency($other);
        return $this->amount->compareTo($other->amount);
    }

    public function equals(Money $other): bool
    {
        return $this->currency === $other->currency
            && $this->amount->isEqualTo($other->amount);
    }

    public function __toString(): string
    {
        return $this->currency . ' ' . $this->amount->stripTrailingZeros();
    }

    private function assertSameCurrency(Money $other): void
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                sprintf('Cannot operate on different currencies: %s and %s', $this->currency, $other->currency)
            );
        }
    }
}
