<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain;

/**
 * Unique identifier for a ledger entry.
 */
final readonly class EntryId
{
    private function __construct(
        private string $id,
    ) {
    }

    public static function fromString(string $id): self
    {
        return new self($id);
    }

    public static function generate(): self
    {
        return new self('entry_' . bin2hex(random_bytes(16)));
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
