<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Events;

interface DomainEventsPublisher
{
    public function publish(DomainEvent $event): void;
}
