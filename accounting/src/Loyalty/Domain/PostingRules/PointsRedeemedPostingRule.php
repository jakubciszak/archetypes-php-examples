<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain\PostingRules;

use SoftwareArchetypes\Accounting\Loyalty\Domain\AccountType;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Entry;
use SoftwareArchetypes\Accounting\Loyalty\Domain\EntryId;
use SoftwareArchetypes\Accounting\Loyalty\Domain\LoyaltyAccount;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Points;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transaction;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transactions\PointsRedeemed;

/**
 * PointsRedeemedPostingRule processes point redemptions.
 *
 * Business Logic:
 * - Create negative entry in ActivePoints
 * - Create positive entry in SpentPoints
 */
final readonly class PointsRedeemedPostingRule implements PostingRule
{
    public function canProcess(Transaction $transaction): bool
    {
        return $transaction instanceof PointsRedeemed;
    }

    public function process(Transaction $transaction, LoyaltyAccount $account): void
    {
        if (!$transaction instanceof PointsRedeemed) {
            throw new \InvalidArgumentException('Transaction must be PointsRedeemed');
        }

        // Check if sufficient active points
        if ($account->activePoints()->compareTo($transaction->points()) < 0) {
            throw new \RuntimeException(
                sprintf(
                    'Insufficient active points for redemption. Available: %d, Required: %d',
                    $account->activePoints()->amount(),
                    $transaction->points()->amount()
                )
            );
        }

        // Deduct from ActivePoints
        $deductEntry = Entry::create(
            EntryId::generate(),
            AccountType::ACTIVE_POINTS,
            Points::of(-$transaction->points()->amount()),
            $transaction->occurredAt(),
            $transaction->transactionId(),
            sprintf(
                'Redemption %s - %s (%d points)',
                $transaction->redemptionId(),
                $transaction->redemptionType(),
                $transaction->points()->amount()
            ),
            $transaction->redemptionId(),
            null,
            array_merge(
                [
                    'redemption_type' => $transaction->redemptionType(),
                ],
                $transaction->metadata()
            )
        );

        $account->addEntry($deductEntry);

        // Add to SpentPoints
        $spentEntry = Entry::create(
            EntryId::generate(),
            AccountType::SPENT_POINTS,
            $transaction->points(),
            $transaction->occurredAt(),
            $transaction->transactionId(),
            sprintf(
                'Spent - %s (%d points)',
                $transaction->redemptionType(),
                $transaction->points()->amount()
            ),
            $transaction->redemptionId(),
            null,
            array_merge(
                [
                    'redemption_type' => $transaction->redemptionType(),
                ],
                $transaction->metadata()
            )
        );

        $account->addEntry($spentEntry);
    }
}
