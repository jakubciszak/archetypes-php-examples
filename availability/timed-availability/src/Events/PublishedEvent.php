<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability\Events;

use DateTimeImmutable;

interface PublishedEvent
{
    public function occurredAt(): DateTimeImmutable;
}
