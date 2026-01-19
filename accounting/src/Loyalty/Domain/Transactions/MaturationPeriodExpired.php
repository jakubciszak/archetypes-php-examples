<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain\Transactions;

use DateTimeImmutable;
use SoftwareArchetypes\Accounting\Loyalty\Domain\AccountType;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transaction;

/**
 * MaturationPeriodExpired represents the activation of pending points
 * after the return period has expired.
 *
 * This transaction will be interpreted by PostingRules to:
 * - Create negative entries in PendingFromPurchases/PendingFromPromos
 * - Create positive entries in ActivePoints
 */
final readonly class MaturationPeriodExpired implements Transaction
{
    /**
     * @param list<string> $entryIds The pending entries to activate
     */
    public function __construct(
        private string $transactionId,
        private string $customerId,
        private AccountType $sourceAccountType,
        private array $entryIds,
        private DateTimeImmutable $occurredAt,
    ) {
        if ($sourceAccountType !== AccountType::PENDING_FROM_PURCHASES
            && $sourceAccountType !== AccountType::PENDING_FROM_PROMOS) {
            throw new \InvalidArgumentException(
                'Source account must be a pending account type'
            );
        }
    }

    public function transactionId(): string
    {
        return $this->transactionId;
    }

    public function customerId(): string
    {
        return $this->customerId;
    }

    public function sourceAccountType(): AccountType
    {
        return $this->sourceAccountType;
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
        return 'maturation_period_expired';
    }

    /**
     * @return array<string, mixed>
     */
    public function data(): array
    {
        return [
            'customer_id' => $this->customerId,
            'source_account_type' => $this->sourceAccountType,
            'entry_ids' => $this->entryIds,
            'occurred_at' => $this->occurredAt,
        ];
    }
}
