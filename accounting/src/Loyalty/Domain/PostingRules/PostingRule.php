<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain\PostingRules;

use SoftwareArchetypes\Accounting\Loyalty\Domain\LoyaltyAccount;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transaction;

/**
 * PostingRule interprets a Transaction and creates appropriate ledger Entries.
 *
 * Based on the Accounting archetype pattern from "Software Archetypes" (Chapter 7).
 *
 * This is the KEY pattern that separates business logic from accounting mechanics.
 *
 * PostingRules:
 * - Examine transaction data
 * - Apply business rules
 * - Create entries in the correct accounts
 * - Handle complex logic (pending periods, reversals, etc.)
 *
 * Different rules for different transaction types.
 */
interface PostingRule
{
    /**
     * Check if this rule can process the given transaction.
     */
    public function canProcess(Transaction $transaction): bool;

    /**
     * Process the transaction and create entries in the account.
     */
    public function process(Transaction $transaction, LoyaltyAccount $account): void;
}
