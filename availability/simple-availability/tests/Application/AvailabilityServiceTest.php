<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Tests\Application;

use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Availability\SimpleAvailability\Application\AvailabilityService;
use SoftwareArchetypes\Availability\SimpleAvailability\Common\SystemClock;
use SoftwareArchetypes\Availability\SimpleAvailability\Domain\AssetId;
use SoftwareArchetypes\Availability\SimpleAvailability\Domain\OwnerId;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\AssetActivated;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\AssetLocked;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\AssetRegistered;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\AssetRegistrationRejected;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\AssetUnlocked;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\AssetWithdrawn;
use SoftwareArchetypes\Availability\SimpleAvailability\Infrastructure\EventPublisher\InMemoryDomainEventsPublisher;
use SoftwareArchetypes\Availability\SimpleAvailability\Infrastructure\Repository\InMemoryAssetAvailabilityRepository;

class AvailabilityServiceTest extends TestCase
{
    private AvailabilityService $service;
    private InMemoryAssetAvailabilityRepository $repository;
    private InMemoryDomainEventsPublisher $eventsPublisher;

    protected function setUp(): void
    {
        $this->repository = new InMemoryAssetAvailabilityRepository();
        $this->eventsPublisher = new InMemoryDomainEventsPublisher();
        $this->service = new AvailabilityService($this->repository, $this->eventsPublisher, new SystemClock());
    }

    public function testCanRegisterNewAsset(): void
    {
        $assetId = AssetId::of('asset-123');

        $result = $this->service->registerAssetWith($assetId);

        $this->assertTrue($result->isSuccess());
        $this->assertInstanceOf(AssetRegistered::class, $result->getSuccess());
        $this->assertTrue($this->eventsPublisher->hasEvent(AssetRegistered::class));
        $this->assertNotNull($this->repository->findById($assetId));
    }

    public function testCannotRegisterDuplicateAsset(): void
    {
        $assetId = AssetId::of('asset-123');
        $this->service->registerAssetWith($assetId);

        $result = $this->service->registerAssetWith($assetId);

        $this->assertTrue($result->isFailure());
        $this->assertInstanceOf(AssetRegistrationRejected::class, $result->getFailure());
    }

    public function testCanActivateRegisteredAsset(): void
    {
        $assetId = AssetId::of('asset-123');
        $this->service->registerAssetWith($assetId);

        $result = $this->service->activate($assetId);

        $this->assertTrue($result->isSuccess());
        $this->assertInstanceOf(AssetActivated::class, $result->getSuccess());
        $this->assertTrue($this->eventsPublisher->hasEvent(AssetActivated::class));
    }

    public function testCanWithdrawAsset(): void
    {
        $assetId = AssetId::of('asset-123');
        $this->service->registerAssetWith($assetId);
        $this->service->activate($assetId);

        $result = $this->service->withdraw($assetId);

        $this->assertTrue($result->isSuccess());
        $this->assertInstanceOf(AssetWithdrawn::class, $result->getSuccess());
        $this->assertTrue($this->eventsPublisher->hasEvent(AssetWithdrawn::class));
    }

    public function testCanLockAsset(): void
    {
        $assetId = AssetId::of('asset-123');
        $ownerId = OwnerId::of('owner-456');
        $this->service->registerAssetWith($assetId);
        $this->service->activate($assetId);

        $result = $this->service->lock($assetId, $ownerId, new DateInterval('PT30M'));

        $this->assertTrue($result->isSuccess());
        $this->assertInstanceOf(AssetLocked::class, $result->getSuccess());
        $this->assertTrue($this->eventsPublisher->hasEvent(AssetLocked::class));
    }

    public function testCanUnlockAsset(): void
    {
        $assetId = AssetId::of('asset-123');
        $ownerId = OwnerId::of('owner-456');
        $this->service->registerAssetWith($assetId);
        $this->service->activate($assetId);
        $this->service->lock($assetId, $ownerId, new DateInterval('PT30M'));

        $result = $this->service->unlock($assetId, $ownerId, new DateTimeImmutable());

        $this->assertTrue($result->isSuccess());
        $this->assertInstanceOf(AssetUnlocked::class, $result->getSuccess());
        $this->assertTrue($this->eventsPublisher->hasEvent(AssetUnlocked::class));
    }

    public function testCanLockIndefinitely(): void
    {
        $assetId = AssetId::of('asset-123');
        $ownerId = OwnerId::of('owner-456');
        $this->service->registerAssetWith($assetId);
        $this->service->activate($assetId);
        $this->service->lock($assetId, $ownerId, new DateInterval('PT30M'));

        $result = $this->service->lockIndefinitely($assetId, $ownerId);

        $this->assertTrue($result->isSuccess());
        $this->assertInstanceOf(AssetLocked::class, $result->getSuccess());
    }

    public function testCompleteWorkflow(): void
    {
        $assetId = AssetId::of('asset-workflow');
        $ownerId = OwnerId::of('owner-workflow');

        // Register
        $result = $this->service->registerAssetWith($assetId);
        $this->assertTrue($result->isSuccess());

        // Activate
        $result = $this->service->activate($assetId);
        $this->assertTrue($result->isSuccess());

        // Lock
        $result = $this->service->lock($assetId, $ownerId, new DateInterval('PT1H'));
        $this->assertTrue($result->isSuccess());

        // Unlock
        $result = $this->service->unlock($assetId, $ownerId, new DateTimeImmutable());
        $this->assertTrue($result->isSuccess());

        // Withdraw
        $result = $this->service->withdraw($assetId);
        $this->assertTrue($result->isSuccess());
    }
}
