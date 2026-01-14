<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party\Events;

final readonly class RoleRemovalSkipped implements PartyRelatedEvent
{
    public function __construct(
        private string $partyId,
        private string $role,
        private string $reason
    ) {
    }

    public static function dueToRoleNotAssigned(string $partyId, string $role): self
    {
        return new self($partyId, $role, 'ROLE_NOT_ASSIGNED');
    }

    public function partyId(): string
    {
        return $this->partyId;
    }

    public function role(): string
    {
        return $this->role;
    }

    public function reason(): string
    {
        return $this->reason;
    }
}
