<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Common;

use DateTimeImmutable;

final class SystemClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
