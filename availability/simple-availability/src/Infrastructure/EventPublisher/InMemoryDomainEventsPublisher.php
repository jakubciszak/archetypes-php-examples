<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Infrastructure\EventPublisher;

use SoftwareArchetypes\Availability\SimpleAvailability\Events\DomainEvent;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\DomainEventsPublisher;

class InMemoryDomainEventsPublisher implements DomainEventsPublisher
{
    /**
     * @var array<DomainEvent>
     */
    private array $events = [];

    public function publish(DomainEvent $event): void
    {
        $this->events[] = $event;
    }

    /**
     * @return array<DomainEvent>
     */
    public function getPublishedEvents(): array
    {
        return $this->events;
    }

    public function clear(): void
    {
        $this->events = [];
    }

    /**
     * @template T of DomainEvent
     * @param class-string<T> $eventClass
     * @return array<T>
     */
    public function getEventsOfType(string $eventClass): array
    {
        return array_filter(
            $this->events,
            fn($event) => $event instanceof $eventClass
        );
    }

    /**
     * @param class-string<DomainEvent> $eventClass
     */
    public function hasEvent(string $eventClass): bool
    {
        return !empty($this->getEventsOfType($eventClass));
    }
}
