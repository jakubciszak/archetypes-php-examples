<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability\Common;

use DateTimeImmutable;

interface Clock
{
    public function now(): DateTimeImmutable;
}
