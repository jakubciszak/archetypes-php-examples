<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain\Transactions;

use DateTimeImmutable;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Points;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transaction;

/**
 * PromotionAwarded represents bonus points from promotional activities.
 *
 * Examples:
 * - Check-in series bonus
 * - Product bonus (SKU booster)
 * - Quick pickup bonus
 *
 * This transaction will be interpreted by PostingRules to create entries
 * in the PendingFromPromos account (or directly to ActivePoints, depending on rules).
 */
final readonly class PromotionAwarded implements Transaction
{
    public function __construct(
        private string $transactionId,
        private string $customerId,
        private string $promotionId,
        private string $promotionType,
        private Points $bonusPoints,
        private bool $immediateActivation,
        private ?string $referenceId, // e.g., purchase_id for product bonus
        private DateTimeImmutable $occurredAt,
        private array $metadata = [],
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

    public function promotionId(): string
    {
        return $this->promotionId;
    }

    public function promotionType(): string
    {
        return $this->promotionType;
    }

    public function bonusPoints(): Points
    {
        return $this->bonusPoints;
    }

    public function immediateActivation(): bool
    {
        return $this->immediateActivation;
    }

    public function referenceId(): ?string
    {
        return $this->referenceId;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function metadata(): array
    {
        return $this->metadata;
    }

    public function type(): string
    {
        return 'promotion_awarded';
    }

    public function data(): array
    {
        return [
            'customer_id' => $this->customerId,
            'promotion_id' => $this->promotionId,
            'promotion_type' => $this->promotionType,
            'bonus_points' => $this->bonusPoints,
            'immediate_activation' => $this->immediateActivation,
            'reference_id' => $this->referenceId,
            'occurred_at' => $this->occurredAt,
            'metadata' => $this->metadata,
        ];
    }
}
