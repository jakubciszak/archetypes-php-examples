<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability\Tests\Domain;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Availability\TimedAvailability\Domain\Owner;
use SoftwareArchetypes\Availability\TimedAvailability\Domain\ResourceAvailability;
use SoftwareArchetypes\Availability\TimedAvailability\Domain\ResourceAvailabilityId;
use SoftwareArchetypes\Availability\TimedAvailability\Domain\ResourceId;
use SoftwareArchetypes\Availability\TimedAvailability\Domain\TimeSlot;

final class ResourceAvailabilityTest extends TestCase
{
    public function testCanCreateResourceAvailability(): void
    {
        $id = ResourceAvailabilityId::newOne();
        $resourceId = ResourceId::newOne();
        $slot = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 11:00:00')
        );

        $availability = new ResourceAvailability($id, $resourceId, $slot);

        self::assertTrue($availability->id()->equals($id));
        self::assertTrue($availability->resourceId()->equals($resourceId));
        self::assertTrue($availability->segment()->equals($slot));
        self::assertTrue($availability->blockedBy()->byNone());
        self::assertFalse($availability->isDisabled());
    }

    public function testCanBlockAvailableResource(): void
    {
        $availability = $this->createAvailableResource();
        $owner = Owner::newOne();

        $result = $availability->block($owner);

        self::assertTrue($result);
        self::assertTrue($availability->blockedBy()->equals($owner));
    }

    public function testCannotBlockAlreadyBlockedResource(): void
    {
        $availability = $this->createAvailableResource();
        $owner1 = Owner::newOne();
        $owner2 = Owner::newOne();

        $availability->block($owner1);
        $result = $availability->block($owner2);

        self::assertFalse($result);
        self::assertTrue($availability->blockedBy()->equals($owner1));
    }

    public function testOwnerCanBlockAgainTheirOwnResource(): void
    {
        $availability = $this->createAvailableResource();
        $owner = Owner::newOne();

        $availability->block($owner);
        $result = $availability->block($owner);

        self::assertTrue($result);
        self::assertTrue($availability->blockedBy()->equals($owner));
    }

    public function testCanReleaseBlockedResourceByOwner(): void
    {
        $availability = $this->createAvailableResource();
        $owner = Owner::newOne();

        $availability->block($owner);
        $result = $availability->release($owner);

        self::assertTrue($result);
        self::assertTrue($availability->blockedBy()->byNone());
    }

    public function testCannotReleaseResourceBlockedByDifferentOwner(): void
    {
        $availability = $this->createAvailableResource();
        $owner1 = Owner::newOne();
        $owner2 = Owner::newOne();

        $availability->block($owner1);
        $result = $availability->release($owner2);

        self::assertFalse($result);
        self::assertTrue($availability->blockedBy()->equals($owner1));
    }

    public function testCanDisableResource(): void
    {
        $availability = $this->createAvailableResource();
        $owner = Owner::newOne();

        $result = $availability->disable($owner);

        self::assertTrue($result);
        self::assertTrue($availability->isDisabled());
        self::assertTrue($availability->isDisabledBy($owner));
    }

    public function testCanDisableAlreadyBlockedResource(): void
    {
        $availability = $this->createAvailableResource();
        $owner1 = Owner::newOne();
        $owner2 = Owner::newOne();

        $availability->block($owner1);
        $result = $availability->disable($owner2);

        self::assertTrue($result);
        self::assertTrue($availability->isDisabled());
        self::assertTrue($availability->isDisabledBy($owner2));
    }

    public function testCannotBlockDisabledResource(): void
    {
        $availability = $this->createAvailableResource();
        $owner1 = Owner::newOne();
        $owner2 = Owner::newOne();

        $availability->disable($owner1);
        $result = $availability->block($owner2);

        self::assertFalse($result);
    }

    public function testOwnerCanBlockTheirDisabledResource(): void
    {
        $availability = $this->createAvailableResource();
        $owner = Owner::newOne();

        $availability->disable($owner);
        $result = $availability->block($owner);

        self::assertFalse($result);
    }

    public function testCanEnableDisabledResourceByOwner(): void
    {
        $availability = $this->createAvailableResource();
        $owner = Owner::newOne();

        $availability->disable($owner);
        $result = $availability->enable($owner);

        self::assertTrue($result);
        self::assertFalse($availability->isDisabled());
        self::assertTrue($availability->blockedBy()->byNone());
    }

    public function testCannotEnableDisabledResourceByDifferentOwner(): void
    {
        $availability = $this->createAvailableResource();
        $owner1 = Owner::newOne();
        $owner2 = Owner::newOne();

        $availability->disable($owner1);
        $result = $availability->enable($owner2);

        self::assertFalse($result);
        self::assertTrue($availability->isDisabled());
        self::assertTrue($availability->isDisabledBy($owner1));
    }

    public function testVersionStartsAtZero(): void
    {
        $availability = $this->createAvailableResource();

        self::assertEquals(0, $availability->version());
    }

    public function testCanCreateResourceAvailabilityWithParent(): void
    {
        $id = ResourceAvailabilityId::newOne();
        $resourceId = ResourceId::newOne();
        $parentId = ResourceId::newOne();
        $slot = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 11:00:00')
        );

        $availability = new ResourceAvailability($id, $resourceId, $parentId, $slot);

        self::assertTrue($availability->resourceParentId()->equals($parentId));
    }

    private function createAvailableResource(): ResourceAvailability
    {
        return new ResourceAvailability(
            ResourceAvailabilityId::newOne(),
            ResourceId::newOne(),
            new TimeSlot(
                new DateTimeImmutable('2024-01-15 10:00:00'),
                new DateTimeImmutable('2024-01-15 11:00:00')
            )
        );
    }
}
