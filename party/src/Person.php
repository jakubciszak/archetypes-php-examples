<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party;

use SoftwareArchetypes\Party\Common\Result;
use SoftwareArchetypes\Party\Common\Version;
use SoftwareArchetypes\Party\Events\PersonRegistered;
use SoftwareArchetypes\Party\Events\PersonalDataUpdated;
use SoftwareArchetypes\Party\Events\PersonalDataUpdateSkipped;

final class Person extends Party
{
    /**
     * @param array<Role> $roles
     * @param array<RegisteredIdentifier> $registeredIdentifiers
     */
    public function __construct(
        PartyId $partyId,
        private PersonalData $personalData,
        array $roles,
        array $registeredIdentifiers,
        Version $version
    ) {
        parent::__construct($partyId, $roles, $registeredIdentifiers, $version);
    }

    /**
     * @return Result<PersonalDataUpdateSkipped, self>
     */
    public function update(PersonalData $personalData): Result
    {
        if ($this->personalData != $personalData) {
            $this->personalData = $personalData;
            $this->register(new PersonalDataUpdated(
                $this->id()->asString(),
                $personalData->firstName(),
                $personalData->lastName()
            ));
        } else {
            $this->register(PersonalDataUpdateSkipped::dueToNoChangeIdentifiedFor(
                $this->id()->asString(),
                $personalData->firstName(),
                $personalData->lastName()
            ));
        }

        return Result::success($this);
    }

    public function personalData(): PersonalData
    {
        return $this->personalData;
    }

    public function toPartyRegisteredEvent(): PersonRegistered
    {
        return new PersonRegistered(
            $this->id()->asString(),
            $this->personalData->firstName(),
            $this->personalData->lastName(),
            array_map(fn($id) => $id->asString(), $this->registeredIdentifiers()),
            array_map(fn($role) => $role->asString(), $this->roles())
        );
    }
}
