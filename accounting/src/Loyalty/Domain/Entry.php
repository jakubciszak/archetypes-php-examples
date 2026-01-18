<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain;

use DateTimeImmutable;

/**
 * Entry represents a single immutable ledger line in the accounting system.
 *
 * Based on the Accounting archetype pattern from "Software Archetypes" (Chapter 7).
 *
 * An Entry records:
 * - The account it affects
 * - The amount (in points)
 * - When it occurred
 * - What caused it (transaction reference)
 * - Why it happened (description)
 * - Related business entity (purchase, promo, etc.)
 *
 * Entries are immutable - once created, they never change.
 * Balance = sum of all entries for an account.
 */
final readonly class Entry
{
    private function __construct(
        private EntryId $id,
        private AccountType $accountType,
        private Points $amount,
        private DateTimeImmutable $effectiveDate,
        private string $transactionId,
        private string $description,
        private ?string $referenceId, // e.g., purchase_id, promo_id
        private ?string $lineItemId,  // for line-level allocation (partial returns)
        private array $metadata,
    ) {
    }

    public static function create(
        EntryId $id,
        AccountType $accountType,
        Points $amount,
        DateTimeImmutable $effectiveDate,
        string $transactionId,
        string $description,
        ?string $referenceId = null,
        ?string $lineItemId = null,
        array $metadata = [],
    ): self {
        return new self(
            $id,
            $accountType,
            $amount,
            $effectiveDate,
            $transactionId,
            $description,
            $referenceId,
            $lineItemId,
            $metadata,
        );
    }

    public function id(): EntryId
    {
        return $this->id;
    }

    public function accountType(): AccountType
    {
        return $this->accountType;
    }

    public function amount(): Points
    {
        return $this->amount;
    }

    public function effectiveDate(): DateTimeImmutable
    {
        return $this->effectiveDate;
    }

    public function transactionId(): string
    {
        return $this->transactionId;
    }

    public function description(): string
    {
        return $this->description;
    }

    public function referenceId(): ?string
    {
        return $this->referenceId;
    }

    public function lineItemId(): ?string
    {
        return $this->lineItemId;
    }

    public function metadata(): array
    {
        return $this->metadata;
    }

    /**
     * Check if this entry is for a specific reference (e.g., purchase).
     */
    public function isForReference(string $referenceId): bool
    {
        return $this->referenceId === $referenceId;
    }

    /**
     * Check if this entry is for a specific line item.
     */
    public function isForLineItem(string $lineItemId): bool
    {
        return $this->lineItemId === $lineItemId;
    }
}
