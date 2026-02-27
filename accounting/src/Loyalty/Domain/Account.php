<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain;

/**
 * Account represents a single sub-account in the loyalty program ledger.
 *
 * Based on the Accounting archetype pattern from "Software Archetypes" (Chapter 7).
 *
 * An Account:
 * - Has a type (PendingFromPurchases, ActivePoints, etc.)
 * - Contains immutable entries
 * - Calculates balance by summing entries
 * - Never mutates existing entries
 */
final class Account
{
    /**
     * @var array<string, Entry>
     */
    private array $entries = [];

    private function __construct(
        private readonly AccountType $type,
    ) {}

    public static function create(AccountType $type): self
    {
        return new self($type);
    }

    public function type(): AccountType
    {
        return $this->type;
    }

    /**
     * Add a new entry to this account.
     */
    public function addEntry(Entry $entry): void
    {
        if ($entry->accountType() !== $this->type) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Entry account type %s does not match account type %s',
                    $entry->accountType()->value,
                    $this->type->value
                )
            );
        }

        $this->entries[$entry->id()->toString()] = $entry;
    }

    /**
     * Calculate current balance by summing all entries.
     */
    public function balance(): Points
    {
        $total = Points::zero();

        foreach ($this->entries as $entry) {
            $total = $total->add($entry->amount());
        }

        return $total;
    }

    /**
     * Get all entries in this account.
     *
     * @return list<Entry>
     */
    public function entries(): array
    {
        return array_values($this->entries);
    }

    /**
     * Find entries for a specific reference (e.g., purchase_id).
     *
     * @return list<Entry>
     */
    public function entriesForReference(string $referenceId): array
    {
        return array_values(
            array_filter(
                $this->entries,
                fn(Entry $entry) => $entry->isForReference($referenceId)
            )
        );
    }

    /**
     * Find entries for a specific line item.
     *
     * @return list<Entry>
     */
    public function entriesForLineItem(string $lineItemId): array
    {
        return array_values(
            array_filter(
                $this->entries,
                fn(Entry $entry) => $entry->isForLineItem($lineItemId)
            )
        );
    }

    /**
     * Calculate balance for a specific reference.
     */
    public function balanceForReference(string $referenceId): Points
    {
        $total = Points::zero();

        foreach ($this->entriesForReference($referenceId) as $entry) {
            $total = $total->add($entry->amount());
        }

        return $total;
    }

    /**
     * Calculate balance for a specific line item.
     */
    public function balanceForLineItem(string $lineItemId): Points
    {
        $total = Points::zero();

        foreach ($this->entriesForLineItem($lineItemId) as $entry) {
            $total = $total->add($entry->amount());
        }

        return $total;
    }
}
