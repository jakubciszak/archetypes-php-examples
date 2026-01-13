<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Events;

use DateTimeImmutable;
use SoftwareArchetypes\Accounting\Money;
use SoftwareArchetypes\Accounting\AccountId;
use SoftwareArchetypes\Accounting\EntryId;
use SoftwareArchetypes\Accounting\TransactionId;

final readonly class CreditEntryRegistered implements AccountingEvent
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
