<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party;

use SoftwareArchetypes\Party\Common\Result;
use SoftwareArchetypes\Party\Common\Version;
use SoftwareArchetypes\Party\Events\EventPublisher;

final class PartiesFacade
{
    public function __construct(
        private readonly PartyRepository $partyRepository,
        private readonly EventPublisher $eventPublisher
    ) {
    }

    /**
     * @param array<Role> $roles
     * @param array<RegisteredIdentifier> $registeredIdentifiers
     * @return Result<string, Person>
     */
    public function registerPerson(
        PersonalData $personalData,
        array $roles = [],
        array $registeredIdentifiers = []
    ): Result {
        try {
            $person = new Person(
                PartyId::random(),
                $personalData,
                $roles,
                $registeredIdentifiers,
                Version::initial()
            );

            $this->partyRepository->save($person);
            $this->eventPublisher->publish($person->toPartyRegisteredEvent());

            return Result::success($person);
        } catch (\Exception $e) {
            return Result::failure($e->getMessage());
        }
    }

    /**
     * @param array<Role> $roles
     * @param array<RegisteredIdentifier> $registeredIdentifiers
     * @return Result<string, Company>
     */
    public function registerCompany(
        OrganizationName $organizationName,
        array $roles = [],
        array $registeredIdentifiers = []
    ): Result {
        try {
            $company = new Company(
                PartyId::random(),
                $organizationName,
                $roles,
                $registeredIdentifiers,
                Version::initial()
            );

            $this->partyRepository->save($company);
            $this->eventPublisher->publish($company->toPartyRegisteredEvent());

            return Result::success($company);
        } catch (\Exception $e) {
            return Result::failure($e->getMessage());
        }
    }

    /**
     * @return Result<string|Events\RoleAdditionSkipped, Party>
     */
    public function addRole(PartyId $partyId, Role $role): Result
    {
        $party = $this->partyRepository->findBy($partyId);

        if ($party === null) {
            return Result::failure("Party not found: " . $partyId->asString());
        }

        $result = $party->addRole($role);

        if ($result->isSuccess()) {
            $this->partyRepository->save($result->getValue());
            $this->eventPublisher->publish($result->getValue()->events());
        }

        return $result;
    }

    /**
     * @return Result<string|Events\RoleRemovalSkipped, Party>
     */
    public function removeRole(PartyId $partyId, Role $role): Result
    {
        $party = $this->partyRepository->findBy($partyId);

        if ($party === null) {
            return Result::failure("Party not found: " . $partyId->asString());
        }

        $result = $party->removeRole($role);

        if ($result->isSuccess()) {
            $this->partyRepository->save($result->getValue());
            $this->eventPublisher->publish($result->getValue()->events());
        }

        return $result;
    }

    /**
     * @return Result<string|Events\RegisteredIdentifierAdditionSkipped, Party>
     */
    public function addIdentifier(PartyId $partyId, RegisteredIdentifier $identifier): Result
    {
        $party = $this->partyRepository->findBy($partyId);

        if ($party === null) {
            return Result::failure("Party not found: " . $partyId->asString());
        }

        $result = $party->addIdentifier($identifier);

        if ($result->isSuccess()) {
            $this->partyRepository->save($result->getValue());
            $this->eventPublisher->publish($result->getValue()->events());
        }

        return $result;
    }

    /**
     * @return Result<string|Events\RegisteredIdentifierRemovalSkipped, Party>
     */
    public function removeIdentifier(PartyId $partyId, RegisteredIdentifier $identifier): Result
    {
        $party = $this->partyRepository->findBy($partyId);

        if ($party === null) {
            return Result::failure("Party not found: " . $partyId->asString());
        }

        $result = $party->removeIdentifier($identifier);

        if ($result->isSuccess()) {
            $this->partyRepository->save($result->getValue());
            $this->eventPublisher->publish($result->getValue()->events());
        }

        return $result;
    }

    /**
     * @return Result<string|Events\PersonalDataUpdateSkipped, Person>
     */
    public function updatePersonalData(PartyId $partyId, PersonalData $personalData): Result
    {
        $party = $this->partyRepository->findByType($partyId, Person::class);

        if ($party === null || !$party instanceof Person) {
            return Result::failure("Person not found: " . $partyId->asString());
        }

        $result = $party->update($personalData);

        if ($result->isSuccess()) {
            $this->partyRepository->save($result->getValue());
            $this->eventPublisher->publish($result->getValue()->events());
        }

        return $result;
    }

    /**
     * @return Result<string|Events\OrganizationNameUpdateSkipped, Organization>
     */
    public function updateOrganizationName(PartyId $partyId, OrganizationName $organizationName): Result
    {
        $party = $this->partyRepository->findByType($partyId, Organization::class);

        if ($party === null || !$party instanceof Organization) {
            return Result::failure("Organization not found: " . $partyId->asString());
        }

        $result = $party->update($organizationName);

        if ($result->isSuccess()) {
            $this->partyRepository->save($result->getValue());
            $this->eventPublisher->publish($result->getValue()->events());
        }

        return $result;
    }
}
