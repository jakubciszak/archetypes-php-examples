<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability\Infrastructure;

use SoftwareArchetypes\Availability\TimedAvailability\Events\EventsPublisher;
use SoftwareArchetypes\Availability\TimedAvailability\Events\PublishedEvent;

final class InMemoryEventsPublisher implements EventsPublisher
{
    /**
     * @var list<PublishedEvent>
     */
    private array $events = [];

    public function publish(PublishedEvent $event): void
    {
        $this->events[] = $event;
    }

    /**
     * @return list<PublishedEvent>
     */
    public function getPublishedEvents(): array
    {
        return $this->events;
    }

    public function clear(): void
    {
        $this->events = [];
    }
}
