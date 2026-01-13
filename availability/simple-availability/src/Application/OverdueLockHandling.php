<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Application;

use SoftwareArchetypes\Availability\SimpleAvailability\Domain\AssetId;

interface OverdueLockHandling
{
    public function processOverdueLocks(): void;

    public function processOverdueLock(AssetId $assetId): void;
}
