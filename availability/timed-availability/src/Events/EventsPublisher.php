<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability\Events;

interface EventsPublisher
{
    public function publish(PublishedEvent $event): void;
}
