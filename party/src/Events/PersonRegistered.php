<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party\Events;

final readonly class PersonRegistered implements PartyRegistered
{
    /**
     * @param array<string> $registeredIdentifiers
     * @param array<string> $roles
     */
    public function __construct(
        private string $partyId,
        private string $firstName,
        private string $lastName,
        private array $registeredIdentifiers,
        private array $roles
    ) {
    }

    public function partyId(): string
    {
        return $this->partyId;
    }

    public function firstName(): string
    {
        return $this->firstName;
    }

    public function lastName(): string
    {
        return $this->lastName;
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
