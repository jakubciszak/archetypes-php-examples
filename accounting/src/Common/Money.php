<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Common;

final readonly class Money
{
    private function __construct(
        private int|float $amount,
    ) {
    }

    public static function of(int|float $amount): self
    {
        return new self($amount);
    }

    public static function zero(): self
    {
        return new self(0);
    }

    public function amount(): int|float
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
        return $this->amount === 0 || $this->amount === 0.0;
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
