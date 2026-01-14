<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party\Events;

final readonly class PersonalDataUpdated implements PartyRelatedEvent
{
    public function __construct(
        private string $partyId,
        private string $firstName,
        private string $lastName
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
}
