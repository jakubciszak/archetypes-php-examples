<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Infrastructure;

use SoftwareArchetypes\Accounting\Events\EventsPublisher;
use SoftwareArchetypes\Accounting\Events\AccountingEvent;

final class InMemoryEventsPublisher implements EventsPublisher
{
    /**
     * @var list<AccountingEvent>
     */
    private array $publishedEvents = [];

    public function publish(AccountingEvent $event): void
    {
        $this->publishedEvents[] = $event;
    }

    /**
     * @return list<AccountingEvent>
     */
    public function getPublishedEvents(): array
    {
        return $this->publishedEvents;
    }

    public function clear(): void
    {
        $this->publishedEvents = [];
    }
}
