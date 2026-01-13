<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Domain;

final class MaintenanceLock implements Lock
{
    private static ?OwnerId $maintenanceOwnerId = null;

    public function ownerId(): OwnerId
    {
        if (self::$maintenanceOwnerId === null) {
            self::$maintenanceOwnerId = OwnerId::of('MAINTENANCE');
        }
        return self::$maintenanceOwnerId;
    }

    public function wasMadeFor(OwnerId $ownerId): bool
    {
        return $this->ownerId()->equals($ownerId);
    }
}
