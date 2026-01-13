<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Domain;

use DateTimeImmutable;

final readonly class OwnerLock implements Lock
{
    public function __construct(
        private OwnerId $owner,
        private DateTimeImmutable $until
    ) {
    }

    public function ownerId(): OwnerId
    {
        return $this->owner;
    }

    public function getUntil(): DateTimeImmutable
    {
        return $this->until;
    }

    public function wasMadeFor(OwnerId $ownerId): bool
    {
        return $this->owner->equals($ownerId);
    }
}
