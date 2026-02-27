<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain\Transactions;

use DateTimeImmutable;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Points;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transaction;

/**
 * PointsRedeemed represents customer using/spending their loyalty points.
 *
 * This transaction will be interpreted by PostingRules to:
 * - Create negative entries in ActivePoints
 * - Create positive entries in SpentPoints
 */
final readonly class PointsRedeemed implements Transaction
{
    /**
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private string $transactionId,
        private string $customerId,
        private Points $points,
        private string $redemptionId,
        private string $redemptionType,
        private DateTimeImmutable $occurredAt,
        private array $metadata = [],
    ) {}

    public function transactionId(): string
    {
        return $this->transactionId;
    }

    public function customerId(): string
    {
        return $this->customerId;
    }

    public function points(): Points
    {
        return $this->points;
    }

    public function redemptionId(): string
    {
        return $this->redemptionId;
    }

    public function redemptionType(): string
    {
        return $this->redemptionType;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }

    public function type(): string
    {
        return 'points_redeemed';
    }

    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return [
            'customer_id' => $this->customerId,
            'points' => $this->points,
            'redemption_id' => $this->redemptionId,
            'redemption_type' => $this->redemptionType,
            'occurred_at' => $this->occurredAt,
            'metadata' => $this->metadata,
        ];
    }
}
