<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party;

use SoftwareArchetypes\Party\Common\Result;
use SoftwareArchetypes\Party\Common\Version;
use SoftwareArchetypes\Party\Events\OrganizationNameUpdated;
use SoftwareArchetypes\Party\Events\OrganizationNameUpdateSkipped;

abstract class Organization extends Party
{
    /**
     * @param array<Role> $roles
     * @param array<RegisteredIdentifier> $registeredIdentifiers
     */
    public function __construct(
        PartyId $partyId,
        private OrganizationName $organizationName,
        array $roles,
        array $registeredIdentifiers,
        Version $version
    ) {
        parent::__construct($partyId, $roles, $registeredIdentifiers, $version);
    }

    /**
     * @return Result<OrganizationNameUpdateSkipped, static>
     */
    public function update(OrganizationName $organizationName): Result
    {
        if ($this->organizationName != $organizationName) {
            $this->organizationName = $organizationName;
            $this->register(new OrganizationNameUpdated(
                $this->id()->asString(),
                $organizationName->value()
            ));
        } else {
            $this->register(OrganizationNameUpdateSkipped::dueToNoChangeIdentifiedFor(
                $this->id()->asString(),
                $organizationName->value()
            ));
        }

        return Result::success($this);
    }

    public function organizationName(): OrganizationName
    {
        return $this->organizationName;
    }
}
