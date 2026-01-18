<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain\PostingRules;

use SoftwareArchetypes\Accounting\Loyalty\Domain\AccountType;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Entry;
use SoftwareArchetypes\Accounting\Loyalty\Domain\EntryId;
use SoftwareArchetypes\Accounting\Loyalty\Domain\LoyaltyAccount;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Points;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transaction;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transactions\MaturationPeriodExpired;

/**
 * MaturationPeriodExpiredPostingRule moves pending points to active.
 *
 * Business Logic:
 * - Create negative entries in source pending account
 * - Create positive entries in ActivePoints
 */
final readonly class MaturationPeriodExpiredPostingRule implements PostingRule
{
    public function canProcess(Transaction $transaction): bool
    {
        return $transaction instanceof MaturationPeriodExpired;
    }

    public function process(Transaction $transaction, LoyaltyAccount $account): void
    {
        if (!$transaction instanceof MaturationPeriodExpired) {
            throw new \InvalidArgumentException('Transaction must be MaturationPeriodExpired');
        }

        $sourceAccount = $account->account($transaction->sourceAccountType());

        // Find and activate each entry
        foreach ($transaction->entryIds() as $entryId) {
            $originalEntry = $this->findEntry($sourceAccount->entries(), $entryId);

            if ($originalEntry === null) {
                continue; // Entry not found (maybe already processed)
            }

            $points = $originalEntry->amount();

            // Create negative entry in source pending account
            $negativeEntry = Entry::create(
                EntryId::generate(),
                $transaction->sourceAccountType(),
                Points::of(-$points->amount()),
                $transaction->occurredAt(),
                $transaction->transactionId(),
                sprintf(
                    'Maturation - Moving %d points to active (ref: %s)',
                    $points->amount(),
                    $originalEntry->referenceId() ?? 'N/A'
                ),
                $originalEntry->referenceId(),
                $originalEntry->lineItemId(),
                [
                    'original_entry_id' => $originalEntry->id()->toString(),
                    'source_account' => $transaction->sourceAccountType()->value,
                ]
            );

            $account->addEntry($negativeEntry);

            // Create positive entry in ActivePoints
            $activeEntry = Entry::create(
                EntryId::generate(),
                AccountType::ACTIVE_POINTS,
                $points,
                $transaction->occurredAt(),
                $transaction->transactionId(),
                sprintf(
                    'Activated - %d points from %s (ref: %s)',
                    $points->amount(),
                    $transaction->sourceAccountType()->displayName(),
                    $originalEntry->referenceId() ?? 'N/A'
                ),
                $originalEntry->referenceId(),
                $originalEntry->lineItemId(),
                [
                    'original_entry_id' => $originalEntry->id()->toString(),
                    'source_account' => $transaction->sourceAccountType()->value,
                ]
            );

            $account->addEntry($activeEntry);
        }
    }

    /**
     * @param list<Entry> $entries
     */
    private function findEntry(array $entries, string $entryId): ?Entry
    {
        foreach ($entries as $entry) {
            if ($entry->id()->toString() === $entryId) {
                return $entry;
            }
        }
        return null;
    }
}
