<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party\Events;

final readonly class RoleAdditionSkipped implements PartyRelatedEvent
{
    public function __construct(
        private string $partyId,
        private string $role,
        private string $reason
    ) {
    }

    public static function dueToRoleAlreadyAssigned(string $partyId, string $role): self
    {
        return new self($partyId, $role, 'ROLE_ALREADY_ASSIGNED');
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
