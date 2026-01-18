<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Events;

use DateTimeImmutable;
use SoftwareArchetypes\Accounting\Loyalty\Domain\LoyaltyAccountId;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Points;

final readonly class PromotionalPointsAwarded implements LoyaltyEvent
{
    public function __construct(
        private LoyaltyAccountId $accountId,
        private string $actionId,
        private string $description,
        private Points $points,
        private DateTimeImmutable $occurredAt,
    ) {
    }

    public function accountId(): LoyaltyAccountId
    {
        return $this->accountId;
    }

    public function actionId(): string
    {
        return $this->actionId;
    }

    public function description(): string
    {
        return $this->description;
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
