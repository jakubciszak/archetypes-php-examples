<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party\Events;

final readonly class RegisteredIdentifierRemovalSkipped implements PartyRelatedEvent
{
    public function __construct(
        private string $partyId,
        private string $identifier,
        private string $reason
    ) {
    }

    public static function dueToIdentifierNotRegistered(string $partyId, string $identifier): self
    {
        return new self($partyId, $identifier, 'IDENTIFIER_NOT_REGISTERED');
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
