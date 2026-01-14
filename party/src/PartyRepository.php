<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party;

interface PartyRepository
{
    public function findBy(PartyId $partyId): ?Party;

    /**
     * @template T of Party
     * @param class-string<T> $partyType
     * @return T|null
     */
    public function findByType(PartyId $partyId, string $partyType): ?Party;

    public function save(Party $party): void;

    public function delete(PartyId $partyId): void;

    /**
     * @return array<Party>
     */
    public function findByIdentifier(RegisteredIdentifier $registeredIdentifier): array;
}
