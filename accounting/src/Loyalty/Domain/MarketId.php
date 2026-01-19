<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain;

/**
 * Represents a market/country identifier for the loyalty program.
 * Different markets may have different conversion rates and return periods.
 */
final readonly class MarketId
{
    private function __construct(
        private string $id,
    ) {}

    public static function fromString(string $id): self
    {
        return new self($id);
    }

    public function toString(): string
    {
        return $this->id;
    }

    public function equals(self $other): bool
    {
        return $this->id === $other->id;
    }
}
