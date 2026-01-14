<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Common;

use DateTimeImmutable;

interface Clock
{
    public function now(): DateTimeImmutable;
}
