<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Common;

use DateTimeImmutable;

final class SystemClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return new DateTimeImmutable();
    }
}
