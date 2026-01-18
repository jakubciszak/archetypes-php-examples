<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain;

/**
 * Represents loyalty points using integer arithmetic.
 * Points are always positive or zero.
 */
final readonly class Points
{
    private function __construct(
        private int $amount,
    ) {
        if ($amount < 0) {
            throw new \InvalidArgumentException('Points amount cannot be negative');
        }
    }

    public static function of(int $amount): self
    {
        return new self($amount);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function amount(): int
    {
        return $this->amount;
    }

    public function add(self $other): self
    {
        return new self($this->amount + $other->amount);
    }

    public function subtract(self $other): self
    {
        if ($this->amount < $other->amount) {
            throw new \InvalidArgumentException('Cannot subtract more points than available');
        }
        return new self($this->amount - $other->amount);
    }

    public function isZero(): bool
    {
        return $this->amount === 0;
    }

    public function isPositive(): bool
    {
        return $this->amount > 0;
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount;
    }

    public function compareTo(self $other): int
    {
        return $this->amount <=> $other->amount;
    }

    public function greaterThan(self $other): bool
    {
        return $this->amount > $other->amount;
    }

    public function greaterThanOrEqual(self $other): bool
    {
        return $this->amount >= $other->amount;
    }
}
