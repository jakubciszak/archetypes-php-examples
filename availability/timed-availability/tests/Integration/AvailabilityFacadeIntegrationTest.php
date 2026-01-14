<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability\Tests\Integration;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Availability\TimedAvailability\AvailabilityFacade;
use SoftwareArchetypes\Availability\TimedAvailability\InMemoryEventsPublisher;
use SoftwareArchetypes\Availability\TimedAvailability\InMemoryResourceAvailabilityRepository;
use SoftwareArchetypes\Availability\TimedAvailability\Owner;
use SoftwareArchetypes\Availability\TimedAvailability\ResourceId;
use SoftwareArchetypes\Availability\TimedAvailability\SystemClock;
use SoftwareArchetypes\Availability\TimedAvailability\TimeSlot;

final class AvailabilityFacadeIntegrationTest extends TestCase
{
    private AvailabilityFacade $facade;
    private InMemoryResourceAvailabilityRepository $repository;
    private InMemoryEventsPublisher $eventsPublisher;

    protected function setUp(): void
    {
        $this->repository = new InMemoryResourceAvailabilityRepository();
        $this->eventsPublisher = new InMemoryEventsPublisher();
        $clock = new SystemClock();

        $this->facade = new AvailabilityFacade(
            $this->repository,
            $this->repository, // repository implements both Repository and ReadModel
            $this->eventsPublisher,
            $clock
        );
    }

    public function testCanCreateResourceSlots(): void
    {
        $resourceId = ResourceId::newOne();
        $timeSlot = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 12:00:00')
        );

        $this->facade->createResourceSlots($resourceId, $timeSlot);

        $availabilities = $this->repository->loadAllWithinSlot($resourceId, $timeSlot);

        self::assertNotEmpty($availabilities);
        self::assertGreaterThan(0, count($availabilities));
    }

    public function testCanCreateResourceSlotsWithParent(): void
    {
        $resourceId = ResourceId::newOne();
        $parentId = ResourceId::newOne();
        $timeSlot = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 11:00:00')
        );

        $this->facade->createResourceSlotsWithParent($resourceId, $parentId, $timeSlot);

        $availabilities = $this->repository->loadAllByParentIdWithinSlot($parentId, $timeSlot);

        self::assertNotEmpty($availabilities);
    }

    public function testCanBlockResourceSlot(): void
    {
        $resourceId = ResourceId::newOne();
        $owner = Owner::newOne();
        $timeSlot = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 11:00:00')
        );

        $this->facade->createResourceSlots($resourceId, $timeSlot);

        $result = $this->facade->block($resourceId, $timeSlot, $owner);

        self::assertTrue($result);
    }

    public function testCannotBlockAlreadyBlockedResource(): void
    {
        $resourceId = ResourceId::newOne();
        $owner1 = Owner::newOne();
        $owner2 = Owner::newOne();
        $timeSlot = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 11:00:00')
        );

        $this->facade->createResourceSlots($resourceId, $timeSlot);
        $this->facade->block($resourceId, $timeSlot, $owner1);

        $result = $this->facade->block($resourceId, $timeSlot, $owner2);

        self::assertFalse($result);
    }

    public function testCanReleaseBlockedResource(): void
    {
        $resourceId = ResourceId::newOne();
        $owner = Owner::newOne();
        $timeSlot = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 11:00:00')
        );

        $this->facade->createResourceSlots($resourceId, $timeSlot);
        $this->facade->block($resourceId, $timeSlot, $owner);

        $result = $this->facade->release($resourceId, $timeSlot, $owner);

        self::assertTrue($result);
    }

    public function testCannotReleaseResourceBlockedByDifferentOwner(): void
    {
        $resourceId = ResourceId::newOne();
        $owner1 = Owner::newOne();
        $owner2 = Owner::newOne();
        $timeSlot = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 11:00:00')
        );

        $this->facade->createResourceSlots($resourceId, $timeSlot);
        $this->facade->block($resourceId, $timeSlot, $owner1);

        $result = $this->facade->release($resourceId, $timeSlot, $owner2);

        self::assertFalse($result);
    }

    public function testCanDisableResource(): void
    {
        $resourceId = ResourceId::newOne();
        $owner = Owner::newOne();
        $timeSlot = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 11:00:00')
        );

        $this->facade->createResourceSlots($resourceId, $timeSlot);

        $result = $this->facade->disable($resourceId, $timeSlot, $owner);

        self::assertTrue($result);
    }

    public function testCanDisableAndCheckDisabledResource(): void
    {
        $resourceId = ResourceId::newOne();
        $owner = Owner::newOne();
        $timeSlot = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 11:00:00')
        );

        $this->facade->createResourceSlots($resourceId, $timeSlot);
        $disableResult = $this->facade->disable($resourceId, $timeSlot, $owner);

        self::assertTrue($disableResult);

        // Verify resource is disabled - cannot be blocked by another owner
        $anotherOwner = Owner::newOne();
        $blockResult = $this->facade->block($resourceId, $timeSlot, $anotherOwner);
        self::assertFalse($blockResult);
    }

    public function testCompleteResourceLifecycle(): void
    {
        $resourceId = ResourceId::newOne();
        $owner = Owner::newOne();
        $timeSlot = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 12:00:00')
        );

        // Create resource slots
        $this->facade->createResourceSlots($resourceId, $timeSlot);
        $availabilities = $this->repository->loadAllWithinSlot($resourceId, $timeSlot);
        self::assertNotEmpty($availabilities);

        // Block resource
        $blockResult = $this->facade->block($resourceId, $timeSlot, $owner);
        self::assertTrue($blockResult);

        // Release resource
        $releaseResult = $this->facade->release($resourceId, $timeSlot, $owner);
        self::assertTrue($releaseResult);

        // Block again after release
        $blockAgainResult = $this->facade->block($resourceId, $timeSlot, $owner);
        self::assertTrue($blockAgainResult);

        // Disable resource
        $disableResult = $this->facade->disable($resourceId, $timeSlot, $owner);
        self::assertTrue($disableResult);
    }

    public function testMultipleResourcesCanBeManagedIndependently(): void
    {
        $resource1 = ResourceId::newOne();
        $resource2 = ResourceId::newOne();
        $owner1 = Owner::newOne();
        $owner2 = Owner::newOne();
        $timeSlot = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 11:00:00')
        );

        // Create slots for both resources
        $this->facade->createResourceSlots($resource1, $timeSlot);
        $this->facade->createResourceSlots($resource2, $timeSlot);

        // Block both resources by different owners
        $block1 = $this->facade->block($resource1, $timeSlot, $owner1);
        $block2 = $this->facade->block($resource2, $timeSlot, $owner2);

        self::assertTrue($block1);
        self::assertTrue($block2);

        // Release first resource
        $release1 = $this->facade->release($resource1, $timeSlot, $owner1);
        self::assertTrue($release1);

        // Second resource should still be blocked
        $block1Again = $this->facade->block($resource1, $timeSlot, $owner2);
        self::assertTrue($block1Again);
    }

    public function testParentChildResourceHierarchy(): void
    {
        $parentId = ResourceId::newOne();
        $childId = ResourceId::newOne();
        $owner = Owner::newOne();
        $timeSlot = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 11:00:00')
        );

        // Create child resource with parent
        $this->facade->createResourceSlotsWithParent($childId, $parentId, $timeSlot);

        // Verify child was created with parent reference
        $childAvailabilities = $this->repository->loadAllByParentIdWithinSlot($parentId, $timeSlot);
        self::assertNotEmpty($childAvailabilities);

        // Block child resource
        $blockResult = $this->facade->block($childId, $timeSlot, $owner);
        self::assertTrue($blockResult);
    }
}
