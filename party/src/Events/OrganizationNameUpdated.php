<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party\Events;

final readonly class OrganizationNameUpdated implements PartyRelatedEvent
{
    public function __construct(
        private string $partyId,
        private string $organizationName
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
}
