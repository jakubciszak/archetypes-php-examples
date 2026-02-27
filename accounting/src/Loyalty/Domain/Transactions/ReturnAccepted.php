<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain\Transactions;

use DateTimeImmutable;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transaction;

/**
 * ReturnAccepted represents a product return.
 *
 * This transaction will be interpreted by PostingRules to:
 * - Reverse entries from PendingFromPurchases (if still pending)
 * - OR create negative entries in ActivePoints and positive in ReversedPoints
 */
final readonly class ReturnAccepted implements Transaction
{
    /**
     * @param list<string> $lineItemIds Line items being returned
     */
    public function __construct(
        private string $transactionId,
        private string $purchaseId,
        private string $customerId,
        private array $lineItemIds,
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

    /**
     * @return list<string>
     */
    public function lineItemIds(): array
    {
        return $this->lineItemIds;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function type(): string
    {
        return 'return_accepted';
    }

    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return [
            'purchase_id' => $this->purchaseId,
            'customer_id' => $this->customerId,
            'line_item_ids' => $this->lineItemIds,
            'occurred_at' => $this->occurredAt,
        ];
    }
}
