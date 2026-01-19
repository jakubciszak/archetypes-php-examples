<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain\PostingRules;

use SoftwareArchetypes\Accounting\Loyalty\Domain\AccountType;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Entry;
use SoftwareArchetypes\Accounting\Loyalty\Domain\EntryId;
use SoftwareArchetypes\Accounting\Loyalty\Domain\LoyaltyAccount;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Points;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transaction;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transactions\PointsExpired;

/**
 * PointsExpiredPostingRule processes point expiration.
 *
 * Business Logic:
 * - Create negative entries in ActivePoints
 * - Create positive entries in ExpiredPoints
 */
final readonly class PointsExpiredPostingRule implements PostingRule
{
    public function canProcess(Transaction $transaction): bool
    {
        return $transaction instanceof PointsExpired;
    }

    public function process(Transaction $transaction, LoyaltyAccount $account): void
    {
        if (!$transaction instanceof PointsExpired) {
            throw new \InvalidArgumentException('Transaction must be PointsExpired');
        }

        $activeAccount = $account->account(AccountType::ACTIVE_POINTS);

        // Find and expire each entry
        foreach ($transaction->entryIds() as $entryId) {
            $originalEntry = $this->findEntry($activeAccount->entries(), $entryId);

            if ($originalEntry === null) {
                continue; // Entry not found
            }

            $points = $originalEntry->amount();

            // Deduct from ActivePoints
            $deductEntry = Entry::create(
                EntryId::generate(),
                AccountType::ACTIVE_POINTS,
                Points::of(-$points->amount()),
                $transaction->occurredAt(),
                $transaction->transactionId(),
                sprintf(
                    'Expiration - %d points expired (ref: %s)',
                    $points->amount(),
                    $originalEntry->referenceId() ?? 'N/A'
                ),
                $originalEntry->referenceId(),
                $originalEntry->lineItemId(),
                [
                    'original_entry_id' => $originalEntry->id()->toString(),
                ]
            );

            $account->addEntry($deductEntry);

            // Add to ExpiredPoints
            $expiredEntry = Entry::create(
                EntryId::generate(),
                AccountType::EXPIRED_POINTS,
                $points,
                $transaction->occurredAt(),
                $transaction->transactionId(),
                sprintf(
                    'Expired - %d points (ref: %s)',
                    $points->amount(),
                    $originalEntry->referenceId() ?? 'N/A'
                ),
                $originalEntry->referenceId(),
                $originalEntry->lineItemId(),
                [
                    'original_entry_id' => $originalEntry->id()->toString(),
                ]
            );

            $account->addEntry($expiredEntry);
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
