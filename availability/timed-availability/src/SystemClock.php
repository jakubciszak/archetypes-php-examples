<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability;

use DateTimeImmutable;

final readonly class SystemClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
