<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain\Transactions;

use DateTimeImmutable;
use SoftwareArchetypes\Accounting\Loyalty\Domain\MarketId;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transaction;
use SoftwareArchetypes\Accounting\Money;

/**
 * PurchaseCompleted represents a customer purchase in the e-commerce system.
 *
 * This transaction will be interpreted by PostingRules to create entries
 * in the PendingFromPurchases account.
 */
final readonly class PurchaseCompleted implements Transaction
{
    /**
     * @param array<string, array{lineItemId: string, amount: Money, productId: string}> $lineItems
     */
    public function __construct(
        private string $transactionId,
        private string $purchaseId,
        private string $customerId,
        private Money $totalAmount,
        private array $lineItems,
        private MarketId $marketId,
        private DateTimeImmutable $occurredAt,
    ) {}

    public function transactionId(): string
    {
        return $this->transactionId;
    }

    public function purchaseId(): string
    {
        return $this->purchaseId;
    }

    public function customerId(): string
    {
        return $this->customerId;
    }

    public function totalAmount(): Money
    {
        return $this->totalAmount;
    }

    /**
     * @return array<string, array{lineItemId: string, amount: Money, productId: string}>
     */
    /**
     * @return array<string, array{lineItemId: string, amount: Money, productId: string}>
     */
    public function lineItems(): array
    {
        return $this->lineItems;
    }

    public function marketId(): MarketId
    {
        return $this->marketId;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function type(): string
    {
        return 'purchase_completed';
    }

    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return [
            'purchase_id' => $this->purchaseId,
            'customer_id' => $this->customerId,
            'total_amount' => $this->totalAmount,
            'line_items' => $this->lineItems,
            'market_id' => $this->marketId,
            'occurred_at' => $this->occurredAt,
        ];
    }
}
