<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Events;

use DateTimeImmutable;
use SoftwareArchetypes\Accounting\Common\Money;
use SoftwareArchetypes\Accounting\Domain\AccountId;
use SoftwareArchetypes\Accounting\Domain\EntryId;
use SoftwareArchetypes\Accounting\Domain\TransactionId;

final readonly class DebitEntryRegistered implements AccountingEvent
{
    public function __construct(
        public EntryId $entryId,
        public TransactionId $transactionId,
        public AccountId $accountId,
        public Money $amount,
        public DateTimeImmutable $occurredAt,
    ) {
    }
}
