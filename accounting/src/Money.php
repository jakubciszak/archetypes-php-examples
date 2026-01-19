<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting;

/**
 * Represents money using integer arithmetic (amount in cents/smallest currency unit).
 * This avoids floating-point precision issues in financial calculations.
 */
final readonly class Money
{
    private function __construct(
        private int $amount,
    ) {}

    /**
     * Creates a Money instance with the given amount in cents.
     */
    public static function of(int $amount): self
    {
        return new self($amount);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    /**
     * Returns the amount in cents (smallest currency unit).
     */
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
        return new self($this->amount - $other->amount);
    }

    public function negate(): self
    {
        return new self(-$this->amount);
    }

    public function isZero(): bool
    {
        return $this->amount === 0;
    }

    public function isPositive(): bool
    {
        return $this->amount > 0;
    }

    public function isNegative(): bool
    {
        return $this->amount < 0;
    }

    public function equals(self $other): bool
    {
        return $this->amount === $other->amount;
    }

    public function compareTo(self $other): int
    {
        return $this->amount <=> $other->amount;
    }
}
