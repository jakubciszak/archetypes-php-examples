<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party\Events;

final readonly class RegisteredIdentifierRemoved implements PartyRelatedEvent
{
    public function __construct(
        private string $partyId,
        private string $identifier
    ) {
    }

    public function partyId(): string
    {
        return $this->partyId;
    }

    public function identifier(): string
    {
        return $this->identifier;
    }
}
