<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain\PostingRules;

use SoftwareArchetypes\Accounting\Loyalty\Domain\AccountType;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Entry;
use SoftwareArchetypes\Accounting\Loyalty\Domain\EntryId;
use SoftwareArchetypes\Accounting\Loyalty\Domain\LoyaltyAccount;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transaction;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transactions\PromotionAwarded;

/**
 * PromotionAwardedPostingRule creates entries for promotional bonus points.
 *
 * Business Logic:
 * - If immediateActivation: create entry in ActivePoints
 * - Otherwise: create entry in PendingFromPromos (subject to maturation)
 */
final readonly class PromotionAwardedPostingRule implements PostingRule
{
    public function canProcess(Transaction $transaction): bool
    {
        return $transaction instanceof PromotionAwarded;
    }

    public function process(Transaction $transaction, LoyaltyAccount $account): void
    {
        if (!$transaction instanceof PromotionAwarded) {
            throw new \InvalidArgumentException('Transaction must be PromotionAwarded');
        }

        $accountType = $transaction->immediateActivation()
            ? AccountType::ACTIVE_POINTS
            : AccountType::PENDING_FROM_PROMOS;

        $entry = Entry::create(
            EntryId::generate(),
            $accountType,
            $transaction->bonusPoints(),
            $transaction->occurredAt(),
            $transaction->transactionId(),
            sprintf(
                'Promotion %s - %s (%d points%s)',
                $transaction->promotionId(),
                $transaction->promotionType(),
                $transaction->bonusPoints()->amount(),
                $transaction->immediateActivation() ? ' - immediate' : ' - pending'
            ),
            $transaction->promotionId(),
            null, // No line item for promotions
            array_merge(
                [
                    'promotion_type' => $transaction->promotionType(),
                    'immediate_activation' => $transaction->immediateActivation(),
                    'reference_id' => $transaction->referenceId(),
                ],
                $transaction->metadata()
            )
        );

        $account->addEntry($entry);
    }
}
