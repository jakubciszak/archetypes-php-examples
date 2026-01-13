<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Commands;

interface Command
{
    public function getType(): string;
}
