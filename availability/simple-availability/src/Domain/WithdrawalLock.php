<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Domain;

final class WithdrawalLock implements Lock
{
    private static ?OwnerId $withdrawalOwnerId = null;

    public function ownerId(): OwnerId
    {
        if (self::$withdrawalOwnerId === null) {
            self::$withdrawalOwnerId = OwnerId::of('WITHDRAWAL');
        }
        return self::$withdrawalOwnerId;
    }

    public function wasMadeFor(OwnerId $ownerId): bool
    {
        return $this->ownerId()->equals($ownerId);
    }
}
