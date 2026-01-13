<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting;

use DateTimeImmutable;
use SoftwareArchetypes\Accounting\Money;

final readonly class AccountCredited implements Entry
{
    public function __construct(
        private EntryId $id,
        private TransactionId $transactionId,
        private DateTimeImmutable $occurredAt,
        private DateTimeImmutable $appliesAt,
        private AccountId $accountId,
        private Money $amount,
    ) {
    }

    public function id(): EntryId
    {
        return $this->id;
    }

    public function transactionId(): TransactionId
    {
        return $this->transactionId;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    public function appliesAt(): DateTimeImmutable
    {
        return $this->appliesAt;
    }

    public function accountId(): AccountId
    {
        return $this->accountId;
    }

    public function amount(): Money
    {
        return $this->amount;
    }
}
