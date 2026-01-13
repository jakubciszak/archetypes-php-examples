<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Domain;

use DateTimeImmutable;
use SoftwareArchetypes\Accounting\Common\Money;

interface Entry
{
    public function id(): EntryId;

    public function transactionId(): TransactionId;

    public function occurredAt(): DateTimeImmutable;

    public function appliesAt(): DateTimeImmutable;

    public function accountId(): AccountId;

    public function amount(): Money;
}
