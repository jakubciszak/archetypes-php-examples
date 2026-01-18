<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain\PostingRules;

use SoftwareArchetypes\Accounting\Loyalty\Domain\AccountType;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Entry;
use SoftwareArchetypes\Accounting\Loyalty\Domain\EntryId;
use SoftwareArchetypes\Accounting\Loyalty\Domain\LoyaltyAccount;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Points;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transaction;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transactions\ReturnAccepted;

/**
 * ReturnAcceptedPostingRule reverses points for returned products.
 *
 * Business Logic:
 * - For each returned line item, find the original entries
 * - If points are still in PendingFromPurchases: create negative entry there
 * - If points moved to ActivePoints: create negative entry in Active and positive in Reversed
 * - Uses line-level allocation for partial returns
 */
final readonly class ReturnAcceptedPostingRule implements PostingRule
{
    public function canProcess(Transaction $transaction): bool
    {
        return $transaction instanceof ReturnAccepted;
    }

    public function process(Transaction $transaction, LoyaltyAccount $account): void
    {
        if (!$transaction instanceof ReturnAccepted) {
            throw new \InvalidArgumentException('Transaction must be ReturnAccepted');
        }

        // Process each returned line item
        foreach ($transaction->lineItemIds() as $lineItemId) {
            $this->reverseLineItem($transaction, $account, $lineItemId);
        }
    }

    private function reverseLineItem(
        ReturnAccepted $transaction,
        LoyaltyAccount $account,
        string $lineItemId
    ): void {
        // First, try to find pending entries for this line item
        $pendingBalance = $account->balanceForLineItem(
            $lineItemId,
            AccountType::PENDING_FROM_PURCHASES
        );

        if ($pendingBalance->isPositive()) {
            // Points are still pending - reverse them from pending account
            $this->reversePendingPoints(
                $transaction,
                $account,
                $lineItemId,
                $pendingBalance
            );
            return;
        }

        // Points must have been activated - reverse from active and move to reversed
        $activeBalance = $account->balanceForLineItem(
            $lineItemId,
            AccountType::ACTIVE_POINTS
        );

        if ($activeBalance->isPositive()) {
            $this->reverseActivePoints(
                $transaction,
                $account,
                $lineItemId,
                $activeBalance
            );
            return;
        }

        // No points found for this line item (maybe already returned?)
        // Could log warning or throw exception depending on business rules
    }

    private function reversePendingPoints(
        ReturnAccepted $transaction,
        LoyaltyAccount $account,
        string $lineItemId,
        Points $points
    ): void {
        // Create negative entry in PendingFromPurchases
        $negativeEntry = Entry::create(
            EntryId::generate(),
            AccountType::PENDING_FROM_PURCHASES,
            Points::of(-$points->amount()), // Negative to reverse
            $transaction->occurredAt(),
            $transaction->transactionId(),
            sprintf(
                'Return - Purchase %s Line %s (reversed %d pending points)',
                $transaction->purchaseId(),
                $lineItemId,
                $points->amount()
            ),
            $transaction->purchaseId(),
            $lineItemId,
            [
                'reversed_from' => 'pending',
                'original_points' => $points->amount(),
            ]
        );

        $account->addEntry($negativeEntry);

        // Record in ReversedPoints for tracking
        $reversedEntry = Entry::create(
            EntryId::generate(),
            AccountType::REVERSED_POINTS,
            $points,
            $transaction->occurredAt(),
            $transaction->transactionId(),
            sprintf(
                'Return - Purchase %s Line %s (%d points reversed from pending)',
                $transaction->purchaseId(),
                $lineItemId,
                $points->amount()
            ),
            $transaction->purchaseId(),
            $lineItemId,
            [
                'reversed_from' => 'pending',
            ]
        );

        $account->addEntry($reversedEntry);
    }

    private function reverseActivePoints(
        ReturnAccepted $transaction,
        LoyaltyAccount $account,
        string $lineItemId,
        Points $points
    ): void {
        // Create negative entry in ActivePoints
        $negativeEntry = Entry::create(
            EntryId::generate(),
            AccountType::ACTIVE_POINTS,
            Points::of(-$points->amount()), // Negative to reverse
            $transaction->occurredAt(),
            $transaction->transactionId(),
            sprintf(
                'Return - Purchase %s Line %s (deducted %d active points)',
                $transaction->purchaseId(),
                $lineItemId,
                $points->amount()
            ),
            $transaction->purchaseId(),
            $lineItemId,
            [
                'reversed_from' => 'active',
                'original_points' => $points->amount(),
            ]
        );

        $account->addEntry($negativeEntry);

        // Record in ReversedPoints for tracking
        $reversedEntry = Entry::create(
            EntryId::generate(),
            AccountType::REVERSED_POINTS,
            $points,
            $transaction->occurredAt(),
            $transaction->transactionId(),
            sprintf(
                'Return - Purchase %s Line %s (%d points reversed from active)',
                $transaction->purchaseId(),
                $lineItemId,
                $points->amount()
            ),
            $transaction->purchaseId(),
            $lineItemId,
            [
                'reversed_from' => 'active',
            ]
        );

        $account->addEntry($reversedEntry);
    }
}
