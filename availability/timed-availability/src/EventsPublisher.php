<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability;

interface EventsPublisher
{
    public function publish(PublishedEvent $event): void;
}
