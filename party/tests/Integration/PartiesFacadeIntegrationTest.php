<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party\Tests\Integration;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Party\Events\PersonRegistered;
use SoftwareArchetypes\Party\Events\CompanyRegistered;
use SoftwareArchetypes\Party\Infrastructure\InMemoryEventsPublisher;
use SoftwareArchetypes\Party\Infrastructure\InMemoryPartyRepository;
use SoftwareArchetypes\Party\OrganizationName;
use SoftwareArchetypes\Party\PartiesFacade;
use SoftwareArchetypes\Party\PersonalData;
use SoftwareArchetypes\Party\Role;

final class PartiesFacadeIntegrationTest extends TestCase
{
    private PartiesFacade $facade;
    private InMemoryPartyRepository $repository;
    private InMemoryEventsPublisher $eventPublisher;

    protected function setUp(): void
    {
        $this->repository = new InMemoryPartyRepository();
        $this->eventPublisher = new InMemoryEventsPublisher();
        $this->facade = new PartiesFacade($this->repository, $this->eventPublisher);
    }

    public function testCanRegisterPerson(): void
    {
        $personalData = PersonalData::from('John', 'Doe');
        $roles = [Role::of('Customer')];

        $result = $this->facade->registerPerson($personalData, $roles);

        self::assertTrue($result->isSuccess());
        $person = $result->getValue();

        self::assertEquals($personalData, $person->personalData());
        self::assertCount(1, $person->roles());

        // Verify event was published
        $events = $this->eventPublisher->getPublishedEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(PersonRegistered::class, $events[0]);
    }

    public function testCanRegisterCompany(): void
    {
        $organizationName = OrganizationName::of('Acme Corporation');
        $roles = [Role::of('Supplier')];

        $result = $this->facade->registerCompany($organizationName, $roles);

        self::assertTrue($result->isSuccess());
        $company = $result->getValue();

        self::assertEquals($organizationName, $company->organizationName());
        self::assertCount(1, $company->roles());

        // Verify event was published
        $events = $this->eventPublisher->getPublishedEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(CompanyRegistered::class, $events[0]);
    }

    public function testCanAddRoleToParty(): void
    {
        $personalData = PersonalData::from('Jane', 'Smith');
        $registerResult = $this->facade->registerPerson($personalData);
        $person = $registerResult->getValue();

        $this->eventPublisher->clear();

        $role = Role::of('Admin');
        $result = $this->facade->addRole($person->id(), $role);

        self::assertTrue($result->isSuccess());
        self::assertContains($role, $result->getValue()->roles());

        // Verify events were published
        $events = $this->eventPublisher->getPublishedEvents();
        self::assertNotEmpty($events);
    }

    public function testCanRemoveRoleFromParty(): void
    {
        $role = Role::of('User');
        $personalData = PersonalData::from('Bob', 'Johnson');
        $registerResult = $this->facade->registerPerson($personalData, [$role]);
        $person = $registerResult->getValue();

        $this->eventPublisher->clear();

        $result = $this->facade->removeRole($person->id(), $role);

        self::assertTrue($result->isSuccess());
        self::assertCount(0, $result->getValue()->roles());
    }

    public function testCanUpdatePersonalData(): void
    {
        $initialData = PersonalData::from('Alice', 'Brown');
        $registerResult = $this->facade->registerPerson($initialData);
        $person = $registerResult->getValue();

        $this->eventPublisher->clear();

        $newData = PersonalData::from('Alice', 'Green');
        $result = $this->facade->updatePersonalData($person->id(), $newData);

        self::assertTrue($result->isSuccess());
        self::assertEquals($newData, $result->getValue()->personalData());

        // Verify events were published
        $events = $this->eventPublisher->getPublishedEvents();
        self::assertNotEmpty($events);
    }

    public function testCanUpdateOrganizationName(): void
    {
        $initialName = OrganizationName::of('Old Corp');
        $registerResult = $this->facade->registerCompany($initialName);
        $company = $registerResult->getValue();

        $this->eventPublisher->clear();

        $newName = OrganizationName::of('New Corp');
        $result = $this->facade->updateOrganizationName($company->id(), $newName);

        self::assertTrue($result->isSuccess());
        self::assertEquals($newName, $result->getValue()->organizationName());

        // Verify events were published
        $events = $this->eventPublisher->getPublishedEvents();
        self::assertNotEmpty($events);
    }

    public function testReturnsFailureWhenPartyNotFound(): void
    {
        $result = $this->facade->addRole(
            \SoftwareArchetypes\Party\PartyId::random(),
            Role::of('Test')
        );

        self::assertTrue($result->isFailure());
    }
}
