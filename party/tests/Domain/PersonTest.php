<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party\Tests\Domain;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Party\Common\Version;
use SoftwareArchetypes\Party\Events\PersonalDataUpdated;
use SoftwareArchetypes\Party\Events\PersonalDataUpdateSkipped;
use SoftwareArchetypes\Party\PartyId;
use SoftwareArchetypes\Party\Person;
use SoftwareArchetypes\Party\PersonalData;
use SoftwareArchetypes\Party\Role;

final class PersonTest extends TestCase
{
    public function testCanBeCreatedWithMinimalData(): void
    {
        $partyId = PartyId::random();
        $personalData = PersonalData::empty();

        $person = new Person($partyId, $personalData, [], [], Version::initial());

        self::assertEquals($partyId, $person->id());
        self::assertEquals($personalData, $person->personalData());
        self::assertCount(0, $person->roles());
        self::assertCount(0, $person->registeredIdentifiers());
    }

    public function testCanUpdatePersonalData(): void
    {
        $person = new Person(
            PartyId::random(),
            PersonalData::empty(),
            [],
            [],
            Version::initial()
        );

        $newData = PersonalData::from('John', 'Doe');
        $result = $person->update($newData);

        self::assertTrue($result->isSuccess());
        self::assertEquals($newData, $result->getValue()->personalData());
    }

    public function testGeneratesPersonalDataUpdatedEventWhenSuccessfullyDefiningData(): void
    {
        $person = new Person(
            PartyId::random(),
            PersonalData::empty(),
            [],
            [],
            Version::initial()
        );

        $personalData = PersonalData::from('Jane', 'Smith');
        $person->update($personalData);

        $events = $person->events();
        $updatedEvents = array_values(array_filter(
            $events,
            fn($event) => $event instanceof PersonalDataUpdated
        ));

        self::assertCount(1, $updatedEvents);

        $event = $updatedEvents[0];
        self::assertEquals($person->id()->asString(), $event->partyId());
        self::assertEquals('Jane', $event->firstName());
        self::assertEquals('Smith', $event->lastName());
    }

    public function testGeneratesPersonalDataUpdateSkippedEventWhenNoChangesAreIdentified(): void
    {
        $data = PersonalData::from('Bob', 'Johnson');
        $person = new Person(
            PartyId::random(),
            $data,
            [],
            [],
            Version::initial()
        );

        $person->update($data);

        $events = $person->events();
        $skippedEvents = array_values(array_filter(
            $events,
            fn($event) => $event instanceof PersonalDataUpdateSkipped
        ));

        self::assertCount(1, $skippedEvents);

        $event = $skippedEvents[0];
        self::assertEquals($person->id()->asString(), $event->partyId());
        self::assertEquals('Bob', $event->firstName());
        self::assertEquals('Johnson', $event->lastName());
        self::assertEquals('NO_CHANGE_IDENTIFIED', $event->reason());
    }

    public function testCanAddRole(): void
    {
        $person = new Person(
            PartyId::random(),
            PersonalData::empty(),
            [],
            [],
            Version::initial()
        );

        $role = Role::of('Customer');
        $result = $person->addRole($role);

        self::assertTrue($result->isSuccess());
        self::assertCount(1, $result->getValue()->roles());
        self::assertContains($role, $result->getValue()->roles());
    }

    public function testCanRemoveRole(): void
    {
        $role = Role::of('Admin');
        $person = new Person(
            PartyId::random(),
            PersonalData::empty(),
            [$role],
            [],
            Version::initial()
        );

        $result = $person->removeRole($role);

        self::assertTrue($result->isSuccess());
        self::assertCount(0, $result->getValue()->roles());
    }
}
