<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party\Tests\Domain;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Party\Common\Version;
use SoftwareArchetypes\Party\Company;
use SoftwareArchetypes\Party\Events\OrganizationNameUpdated;
use SoftwareArchetypes\Party\Events\OrganizationNameUpdateSkipped;
use SoftwareArchetypes\Party\OrganizationName;
use SoftwareArchetypes\Party\PartyId;
use SoftwareArchetypes\Party\Role;

final class CompanyTest extends TestCase
{
    public function testCanBeCreatedWithMinimalData(): void
    {
        $partyId = PartyId::random();
        $organizationName = OrganizationName::of('Acme Corp');

        $company = new Company($partyId, $organizationName, [], [], Version::initial());

        self::assertEquals($partyId, $company->id());
        self::assertEquals($organizationName, $company->organizationName());
        self::assertCount(0, $company->roles());
        self::assertCount(0, $company->registeredIdentifiers());
    }

    public function testCanUpdateOrganizationName(): void
    {
        $company = new Company(
            PartyId::random(),
            OrganizationName::of('Old Name'),
            [],
            [],
            Version::initial()
        );

        $newName = OrganizationName::of('New Name');
        $result = $company->update($newName);

        self::assertTrue($result->isSuccess());
        self::assertEquals($newName, $result->getValue()->organizationName());
    }

    public function testGeneratesOrganizationNameUpdatedEventWhenSuccessfullyDefiningName(): void
    {
        $company = new Company(
            PartyId::random(),
            OrganizationName::of('Initial Name'),
            [],
            [],
            Version::initial()
        );

        $organizationName = OrganizationName::of('Updated Company');
        $company->update($organizationName);

        $events = $company->events();
        $updatedEvents = array_values(array_filter(
            $events,
            fn($event) => $event instanceof OrganizationNameUpdated
        ));

        self::assertCount(1, $updatedEvents);

        $event = $updatedEvents[0];
        self::assertEquals($company->id()->asString(), $event->partyId());
        self::assertEquals('Updated Company', $event->organizationName());
    }

    public function testGeneratesOrganizationNameUpdateSkippedEventWhenNoChangesAreIdentified(): void
    {
        $name = OrganizationName::of('Stable Inc');
        $company = new Company(
            PartyId::random(),
            $name,
            [],
            [],
            Version::initial()
        );

        $company->update($name);

        $events = $company->events();
        $skippedEvents = array_values(array_filter(
            $events,
            fn($event) => $event instanceof OrganizationNameUpdateSkipped
        ));

        self::assertCount(1, $skippedEvents);

        $event = $skippedEvents[0];
        self::assertEquals($company->id()->asString(), $event->partyId());
        self::assertEquals('Stable Inc', $event->organizationName());
        self::assertEquals('NO_CHANGE_IDENTIFIED', $event->reason());
    }

    public function testCanAddRole(): void
    {
        $company = new Company(
            PartyId::random(),
            OrganizationName::of('Test Company'),
            [],
            [],
            Version::initial()
        );

        $role = Role::of('Supplier');
        $result = $company->addRole($role);

        self::assertTrue($result->isSuccess());
        self::assertCount(1, $result->getValue()->roles());
        self::assertContains($role, $result->getValue()->roles());
    }
}
