<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party\Events;

final readonly class PersonalDataUpdateSkipped implements PartyRelatedEvent
{
    public function __construct(
        private string $partyId,
        private string $firstName,
        private string $lastName,
        private string $reason
    ) {
    }

    public static function dueToNoChangeIdentifiedFor(
        string $partyId,
        string $firstName,
        string $lastName
    ): self {
        return new self($partyId, $firstName, $lastName, 'NO_CHANGE_IDENTIFIED');
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

    public function reason(): string
    {
        return $this->reason;
    }
}
