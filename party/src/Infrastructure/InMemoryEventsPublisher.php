<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party\Infrastructure;

use SoftwareArchetypes\Party\Events\EventPublisher;
use SoftwareArchetypes\Party\Events\PartyRelatedEvent;

final class InMemoryEventsPublisher implements EventPublisher
{
    /**
     * @var array<PartyRelatedEvent>
     */
    private array $publishedEvents = [];

    public function publish(PartyRelatedEvent|array $events): void
    {
        if (is_array($events)) {
            foreach ($events as $event) {
                $this->publishedEvents[] = $event;
            }
        } else {
            $this->publishedEvents[] = $events;
        }
    }

    /**
     * @return array<PartyRelatedEvent>
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
