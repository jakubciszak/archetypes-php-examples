<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain;

/**
 * Represents loyalty points using integer arithmetic.
 *
 * In an entry-based ledger system, Points can be negative to represent
 * reversals, deductions, and contra-entries (debits/credits).
 *
 * For example:
 * - Earning points: +100
 * - Spending points: -100
 * - Reversing a transaction: negative of original amount
 */
final readonly class Points
{
    private function __construct(
        private int $amount,
    ) {}

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
