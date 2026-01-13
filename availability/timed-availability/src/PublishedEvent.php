<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability;

use DateTimeImmutable;

interface PublishedEvent
{
    public function occurredAt(): DateTimeImmutable;
}
