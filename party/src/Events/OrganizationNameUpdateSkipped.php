<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party\Events;

final readonly class OrganizationNameUpdateSkipped implements PartyRelatedEvent
{
    public function __construct(
        private string $partyId,
        private string $organizationName,
        private string $reason
    ) {
    }

    public static function dueToNoChangeIdentifiedFor(
        string $partyId,
        string $organizationName
    ): self {
        return new self($partyId, $organizationName, 'NO_CHANGE_IDENTIFIED');
    }

    public function partyId(): string
    {
        return $this->partyId;
    }

    public function organizationName(): string
    {
        return $this->organizationName;
    }

    public function reason(): string
    {
        return $this->reason;
    }
}
