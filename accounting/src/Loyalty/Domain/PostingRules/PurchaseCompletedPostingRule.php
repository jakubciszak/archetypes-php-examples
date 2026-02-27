<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain\PostingRules;

use SoftwareArchetypes\Accounting\Loyalty\Domain\AccountType;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Entry;
use SoftwareArchetypes\Accounting\Loyalty\Domain\EntryId;
use SoftwareArchetypes\Accounting\Loyalty\Domain\LoyaltyAccount;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transaction;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transactions\PurchaseCompleted;

/**
 * PurchaseCompletedPostingRule creates pending points entries for purchases.
 *
 * Business Logic:
 * - Calculate points based on AccountingPractice (conversion rate, product bonuses)
 * - Create entries in PendingFromPurchases account
 * - Track line-level details for partial returns
 * - Add maturation date metadata for activation
 */
final readonly class PurchaseCompletedPostingRule implements PostingRule
{
    public function canProcess(Transaction $transaction): bool
    {
        return $transaction instanceof PurchaseCompleted;
    }

    public function process(Transaction $transaction, LoyaltyAccount $account): void
    {
        if (!$transaction instanceof PurchaseCompleted) {
            throw new \InvalidArgumentException('Transaction must be PurchaseCompleted');
        }

        $practice = $account->accountingPractice();

        // Process each line item separately for line-level allocation
        foreach ($transaction->lineItems() as $lineItem) {
            $lineItemId = $lineItem['lineItemId'];
            $amount = $lineItem['amount'];
            $productId = $lineItem['productId'];

            // Calculate points for this line item
            $points = $practice->calculatePoints($amount, $productId);

            if ($points->isZero()) {
                continue; // Skip if no points
            }

            // Calculate maturation date (when points become active)
            $maturationDate = $transaction->occurredAt()->modify(
                sprintf('+%d days', $practice->maturationPeriodDays())
            );

            // Create entry in PendingFromPurchases
            $entry = Entry::create(
                EntryId::generate(),
                AccountType::PENDING_FROM_PURCHASES,
                $points,
                $transaction->occurredAt(),
                $transaction->transactionId(),
                sprintf(
                    'Purchase %s - Line %s (%d points pending until %s)',
                    $transaction->purchaseId(),
                    $lineItemId,
                    $points->amount(),
                    $maturationDate->format('Y-m-d')
                ),
                $transaction->purchaseId(),
                $lineItemId,
                [
                    'market_id' => $transaction->marketId()->toString(),
                    'maturation_date' => $maturationDate->format('Y-m-d H:i:s'),
                    'product_id' => $productId,
                    'amount' => $amount->amount(),
                    'conversion_rate' => $practice->pointsPerCurrencyUnit(),
                ]
            );

            $account->addEntry($entry);
        }
    }
}
