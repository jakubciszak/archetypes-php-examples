<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party\Infrastructure;

use SoftwareArchetypes\Party\Party;
use SoftwareArchetypes\Party\PartyId;
use SoftwareArchetypes\Party\PartyRepository;
use SoftwareArchetypes\Party\RegisteredIdentifier;

final class InMemoryPartyRepository implements PartyRepository
{
    /**
     * @var array<string, Party>
     */
    private array $parties = [];

    public function findBy(PartyId $partyId): ?Party
    {
        return $this->parties[$partyId->asString()] ?? null;
    }

    public function findByType(PartyId $partyId, string $partyType): ?Party
    {
        $party = $this->findBy($partyId);

        if ($party === null) {
            return null;
        }

        return $party instanceof $partyType ? $party : null;
    }

    public function save(Party $party): void
    {
        $this->parties[$party->id()->asString()] = $party;
    }

    public function delete(PartyId $partyId): void
    {
        unset($this->parties[$partyId->asString()]);
    }

    /**
     * @return array<Party>
     */
    public function findByIdentifier(RegisteredIdentifier $registeredIdentifier): array
    {
        $result = [];

        foreach ($this->parties as $party) {
            foreach ($party->registeredIdentifiers() as $identifier) {
                if ($identifier->asString() === $registeredIdentifier->asString()) {
                    $result[] = $party;
                    break;
                }
            }
        }

        return $result;
    }
}
