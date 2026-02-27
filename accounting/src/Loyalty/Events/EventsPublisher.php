<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Events;

interface EventsPublisher
{
    public function publish(LoyaltyEvent $event): void;
}
