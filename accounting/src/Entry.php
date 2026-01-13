<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting;

use DateTimeImmutable;
use SoftwareArchetypes\Accounting\Money;

interface Entry
{
    public function id(): EntryId;

    public function transactionId(): TransactionId;

    public function occurredAt(): DateTimeImmutable;

    public function appliesAt(): DateTimeImmutable;

    public function accountId(): AccountId;

    public function amount(): Money;
}
