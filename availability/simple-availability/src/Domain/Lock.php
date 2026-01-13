<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Domain;

interface Lock
{
    public function ownerId(): OwnerId;

    public function wasMadeFor(OwnerId $ownerId): bool;
}
