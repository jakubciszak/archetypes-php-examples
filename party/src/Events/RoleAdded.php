<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party\Events;

final readonly class RoleAdded implements PartyRelatedEvent
{
    public function __construct(
        private string $partyId,
        private string $role
    ) {
    }

    public function partyId(): string
    {
        return $this->partyId;
    }

    public function role(): string
    {
        return $this->role;
    }
}
