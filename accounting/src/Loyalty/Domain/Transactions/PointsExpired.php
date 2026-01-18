<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain\Transactions;

use DateTimeImmutable;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transaction;

/**
 * PointsExpired represents points that expired before being used.
 *
 * This transaction will be interpreted by PostingRules to:
 * - Create negative entries in ActivePoints
 * - Create positive entries in ExpiredPoints
 */
final readonly class PointsExpired implements Transaction
{
    /**
     * @param list<string> $entryIds The active entries that are expiring
     */
    public function __construct(
        private string $transactionId,
        private string $customerId,
        private array $entryIds,
        private DateTimeImmutable $occurredAt,
    ) {
    }

    public function transactionId(): string
    {
        return $this->transactionId;
    }

    public function customerId(): string
    {
        return $this->customerId;
    }

    /**
     * @return list<string>
     */
    public function entryIds(): array
    {
        return $this->entryIds;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function type(): string
    {
        return 'points_expired';
    }

    public function data(): array
    {
        return [
            'customer_id' => $this->customerId,
            'entry_ids' => $this->entryIds,
            'occurred_at' => $this->occurredAt,
        ];
    }
}
