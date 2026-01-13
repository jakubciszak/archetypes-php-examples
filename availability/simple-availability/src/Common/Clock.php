<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Common;

use DateTimeImmutable;

interface Clock
{
    public function now(): DateTimeImmutable;
}
