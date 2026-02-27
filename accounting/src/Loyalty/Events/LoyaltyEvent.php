<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Events;

use DateTimeImmutable;
use SoftwareArchetypes\Accounting\Loyalty\Domain\LoyaltyAccountId;

interface LoyaltyEvent
{
    public function accountId(): LoyaltyAccountId;

    public function occurredAt(): DateTimeImmutable;
}
