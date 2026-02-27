<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain;

use DateTimeImmutable;

/**
 * Transaction represents a domain event that will be interpreted into ledger entries.
 *
 * Based on the Accounting archetype pattern from "Software Archetypes" (Chapter 7).
 *
 * Transactions are business facts (PurchaseCompleted, ReturnAccepted), NOT accounting entries.
 * PostingRules interpret transactions into entries.
 *
 * This is the separation between business logic and accounting mechanics.
 */
interface Transaction
{
    public function transactionId(): string;

    public function occurredAt(): DateTimeImmutable;

    public function type(): string;

    /**
     * Get all data needed for posting rules.
     *
     * @return array<string, mixed>
     */
    public function data(): array;
}
