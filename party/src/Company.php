<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party;

use SoftwareArchetypes\Party\Common\Version;
use SoftwareArchetypes\Party\Events\CompanyRegistered;

final class Company extends Organization
{
    /**
     * @param array<Role> $roles
     * @param array<RegisteredIdentifier> $registeredIdentifiers
     */
    public function __construct(
        PartyId $partyId,
        OrganizationName $organizationName,
        array $roles,
        array $registeredIdentifiers,
        Version $version
    ) {
        parent::__construct($partyId, $organizationName, $roles, $registeredIdentifiers, $version);
    }

    public function toPartyRegisteredEvent(): CompanyRegistered
    {
        return new CompanyRegistered(
            $this->id()->asString(),
            $this->organizationName()->value(),
            array_map(fn($id) => $id->asString(), $this->registeredIdentifiers()),
            array_map(fn($role) => $role->asString(), $this->roles())
        );
    }
}
