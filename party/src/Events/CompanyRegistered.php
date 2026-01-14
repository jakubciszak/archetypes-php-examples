<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party\Events;

final readonly class CompanyRegistered implements PartyRegistered
{
    /**
     * @param array<string> $registeredIdentifiers
     * @param array<string> $roles
     */
    public function __construct(
        private string $partyId,
        private string $organizationName,
        private array $registeredIdentifiers,
        private array $roles
    ) {
    }

    public function partyId(): string
    {
        return $this->partyId;
    }

    public function organizationName(): string
    {
        return $this->organizationName;
    }

    public function registeredIdentifiers(): array
    {
        return $this->registeredIdentifiers;
    }

    public function roles(): array
    {
        return $this->roles;
    }
}
