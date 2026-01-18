<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Events;

use DateTimeImmutable;
use SoftwareArchetypes\Accounting\Loyalty\Domain\LoyaltyAccountId;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Points;
use SoftwareArchetypes\Accounting\Loyalty\Domain\PurchaseId;

final readonly class PointsActivated implements LoyaltyEvent
{
    public function __construct(
        private LoyaltyAccountId $accountId,
        private PurchaseId $purchaseId,
        private Points $points,
        private DateTimeImmutable $occurredAt,
    ) {
    }

    public function accountId(): LoyaltyAccountId
    {
        return $this->accountId;
    }

    public function purchaseId(): PurchaseId
    {
        return $this->purchaseId;
    }

    public function points(): Points
    {
        return $this->points;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
