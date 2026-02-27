<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Infrastructure;

use SoftwareArchetypes\Accounting\Loyalty\Events\EventsPublisher;
use SoftwareArchetypes\Accounting\Loyalty\Events\LoyaltyEvent;

final class InMemoryEventsPublisher implements EventsPublisher
{
    /**
     * @var list<LoyaltyEvent>
     */
    private array $publishedEvents = [];

    public function publish(LoyaltyEvent $event): void
    {
        $this->publishedEvents[] = $event;
    }

    /**
     * @return list<LoyaltyEvent>
     */
    public function publishedEvents(): array
    {
        return $this->publishedEvents;
    }

    public function clear(): void
    {
        $this->publishedEvents = [];
    }
}
