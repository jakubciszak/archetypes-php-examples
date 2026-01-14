<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party\Events;

final readonly class RegisteredIdentifierAdditionSkipped implements PartyRelatedEvent
{
    public function __construct(
        private string $partyId,
        private string $identifier,
        private string $reason
    ) {
    }

    public static function dueToIdentifierAlreadyRegistered(string $partyId, string $identifier): self
    {
        return new self($partyId, $identifier, 'IDENTIFIER_ALREADY_REGISTERED');
    }

    public function partyId(): string
    {
        return $this->partyId;
    }

    public function identifier(): string
    {
        return $this->identifier;
    }

    public function reason(): string
    {
        return $this->reason;
    }
}
