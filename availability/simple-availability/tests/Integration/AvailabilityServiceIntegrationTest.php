<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Tests\Integration;

use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Availability\SimpleAvailability\Application\AvailabilityService;
use SoftwareArchetypes\Availability\SimpleAvailability\Common\Clock;
use SoftwareArchetypes\Availability\SimpleAvailability\Tests\Support\MockClock;
use SoftwareArchetypes\Availability\SimpleAvailability\Domain\AssetId;
use SoftwareArchetypes\Availability\SimpleAvailability\Domain\OwnerId;
use SoftwareArchetypes\Availability\SimpleAvailability\Infrastructure\EventPublisher\InMemoryDomainEventsPublisher;
use SoftwareArchetypes\Availability\SimpleAvailability\Infrastructure\Repository\InMemoryAssetAvailabilityRepository;
use SoftwareArchetypes\Availability\SimpleAvailability\Tests\Fixtures\AssetAvailabilityFixture;
use SoftwareArchetypes\Availability\SimpleAvailability\Tests\Support\AssetAvailabilityEventsSupport;
use SoftwareArchetypes\Availability\SimpleAvailability\Tests\Support\AssetAvailabilityStoreSupport;

class AvailabilityServiceIntegrationTest extends TestCase
{
    use AssetAvailabilityStoreSupport;
    use AssetAvailabilityEventsSupport;

    private AvailabilityService $service;
    private InMemoryAssetAvailabilityRepository $repository;
    private MockClock $clock;

    protected function setUp(): void
    {
        $this->repository = new InMemoryAssetAvailabilityRepository();
        $this->setupEventPublisher();
        $this->clock = new MockClock();
        $this->service = new AvailabilityService($this->repository, $this->eventPublisher, $this->clock);
    }

    protected function getRepository(): InMemoryAssetAvailabilityRepository
    {
        return $this->repository;
    }

    protected function getClock(): Clock
    {
        return $this->clock;
    }

    // REGISTRATION TESTS

    public function testShouldAcceptRegistrationOfNewAsset(): void
    {
        // given
        $assetId = AssetId::of(AssetAvailabilityFixture::someAssetIdValue());

        // when
        $result = $this->service->registerAssetWith($assetId);

        // then
        $this->assertTrue($result->isSuccess());
        $this->assertTrue($this->assetIsRegisteredWith($assetId));
        $this->assertTrue($this->assetRegisteredEventWasPublishedFor($assetId));
    }

    public function testShouldRejectRegistrationOfAlreadyExistingAsset(): void
    {
        // given
        $asset = $this->existingAsset();

        // when
        $result = $this->service->registerAssetWith($asset->id());

        // then
        $this->assertTrue($result->isFailure());
    }

    // WITHDRAWAL TESTS

    public function testShouldAcceptWithdrawalOfExistingAsset(): void
    {
        // given
        $asset = $this->existingAsset();

        // when
        $result = $this->service->withdraw($asset->id());

        // then
        $this->assertTrue($result->isSuccess());
        $this->assertTrue($this->thereIsAWithdrawnAssetWith($asset->id()));
        $this->assertTrue($this->assetWithdrawnEventWasPublishedFor($asset->id()));
    }

    public function testShouldRejectWithdrawalOfLockedAsset(): void
    {
        // given
        $asset = $this->lockedAsset();

        // when
        $result = $this->service->withdraw($asset->id());

        // then
        $this->assertTrue($result->isFailure());
    }

    // ACTIVATION TESTS

    public function testShouldAcceptActivationOfRegisteredAsset(): void
    {
        // given
        $asset = $this->existingAsset();

        // when
        $result = $this->service->activate($asset->id());

        // then
        $this->assertTrue($result->isSuccess());
    }

    public function testShouldRejectActivationOfNotExistingAsset(): void
    {
        // given
        $idOfNotExistingAsset = AssetId::of(AssetAvailabilityFixture::someAssetIdValue());

        // when
        $result = $this->service->activate($idOfNotExistingAsset);

        // then
        $this->assertTrue($result->isFailure());
    }

    // LOCKING TESTS

    public function testShouldAcceptLockingOfActivatedAsset(): void
    {
        // given
        $asset = $this->activatedAsset();
        $ownerId = OwnerId::of('ADAM_123');
        $duration = AssetAvailabilityFixture::someValidDuration();

        // when
        $result = $this->service->lock($asset->id(), $ownerId, $duration);

        // then
        $this->assertTrue($result->isSuccess());
        $this->assertTrue($this->thereIsALockedAssetWith($asset->id(), $ownerId));
        $this->assertTrue($this->assetLockedEventWasPublishedFor($asset->id(), $ownerId));
    }

    public function testShouldRejectLockingOfAlreadyLockedAsset(): void
    {
        // given
        $asset = $this->lockedAsset();
        $anotherOwnerId = OwnerId::of('ADAM_123');
        $duration = AssetAvailabilityFixture::someValidDuration();

        // when
        $result = $this->service->lock($asset->id(), $anotherOwnerId, $duration);

        // then
        $this->assertTrue($result->isFailure());
    }

    public function testShouldAcceptIndefiniteLockingOfAlreadyLockedAsset(): void
    {
        // given
        $ownerId = OwnerId::of('ADAM_123');
        $asset = $this->assetLockedBy($ownerId);

        // when
        $result = $this->service->lockIndefinitely($asset->id(), $ownerId);

        // then
        $this->assertTrue($result->isSuccess());
        $this->assertTrue($this->thereIsALockedAssetWith($asset->id(), $ownerId));
        $this->assertTrue($this->assetLockedEventWasPublishedFor($asset->id(), $ownerId));
    }

    public function testShouldRejectIndefiniteLockingOfAssetLockedBySomeoneElse(): void
    {
        // given
        $asset = $this->lockedAsset();
        $differentOwnerId = OwnerId::of('ADAM_123');

        // when
        $result = $this->service->lockIndefinitely($asset->id(), $differentOwnerId);

        // then
        $this->assertTrue($result->isFailure());
    }

    // UNLOCKING TESTS

    public function testShouldAcceptUnlockingOfAlreadyLockedAsset(): void
    {
        // given
        $ownerId = OwnerId::of('ADAM_123');
        $asset = $this->assetLockedBy($ownerId);
        $at = new DateTimeImmutable();

        // when
        $result = $this->service->unlock($asset->id(), $ownerId, $at);

        // then
        $this->assertTrue($result->isSuccess());
        $this->assertTrue($this->thereIsAnUnlockedAssetWith($asset->id()));
        $this->assertTrue($this->assetUnlockedEventWasPublishedFor($asset->id(), $ownerId));
    }

    public function testShouldRejectUnlockingOfAssetLockedBySomeoneElse(): void
    {
        // given
        $asset = $this->lockedAsset();
        $differentOwnerId = OwnerId::of('ADAM_123');
        $at = new DateTimeImmutable();

        // when
        $result = $this->service->unlock($asset->id(), $differentOwnerId, $at);

        // then
        $this->assertTrue($result->isFailure());
    }

    // COMPLEX WORKFLOW TESTS

    public function testCompleteAssetLifecycleWorkflow(): void
    {
        // Register
        $assetId = AssetId::of(AssetAvailabilityFixture::someAssetIdValue());
        $result = $this->service->registerAssetWith($assetId);
        $this->assertTrue($result->isSuccess());
        $this->assertTrue($this->assetIsRegisteredWith($assetId));

        // Activate
        $result = $this->service->activate($assetId);
        $this->assertTrue($result->isSuccess());

        // Lock by first owner
        $ownerId1 = OwnerId::of('OWNER_1');
        $result = $this->service->lock($assetId, $ownerId1, new DateInterval('PT1H'));
        $this->assertTrue($result->isSuccess());
        $this->assertTrue($this->thereIsALockedAssetWith($assetId, $ownerId1));

        // Try to lock by second owner (should fail)
        $ownerId2 = OwnerId::of('OWNER_2');
        $result = $this->service->lock($assetId, $ownerId2, new DateInterval('PT1H'));
        $this->assertTrue($result->isFailure());

        // Lock indefinitely by same owner (should succeed)
        $result = $this->service->lockIndefinitely($assetId, $ownerId1);
        $this->assertTrue($result->isSuccess());

        // Unlock by owner
        $result = $this->service->unlock($assetId, $ownerId1, new DateTimeImmutable());
        $this->assertTrue($result->isSuccess());
        $this->assertTrue($this->thereIsAnUnlockedAssetWith($assetId));

        // Withdraw
        $result = $this->service->withdraw($assetId);
        $this->assertTrue($result->isSuccess());
        $this->assertTrue($this->thereIsAWithdrawnAssetWith($assetId));
    }

    public function testMultipleAssetsCanBeManagedIndependently(): void
    {
        // Register three assets
        $assetId1 = AssetId::of(AssetAvailabilityFixture::someAssetIdValue());
        $assetId2 = AssetId::of(AssetAvailabilityFixture::someAssetIdValue());
        $assetId3 = AssetId::of(AssetAvailabilityFixture::someAssetIdValue());

        $this->service->registerAssetWith($assetId1);
        $this->service->registerAssetWith($assetId2);
        $this->service->registerAssetWith($assetId3);

        // Activate all
        $this->service->activate($assetId1);
        $this->service->activate($assetId2);
        $this->service->activate($assetId3);

        // Lock by different owners
        $owner1 = OwnerId::of('OWNER_1');
        $owner2 = OwnerId::of('OWNER_2');
        $owner3 = OwnerId::of('OWNER_3');

        $result1 = $this->service->lock($assetId1, $owner1, new DateInterval('PT30M'));
        $result2 = $this->service->lock($assetId2, $owner2, new DateInterval('PT30M'));
        $result3 = $this->service->lock($assetId3, $owner3, new DateInterval('PT30M'));

        $this->assertTrue($result1->isSuccess());
        $this->assertTrue($result2->isSuccess());
        $this->assertTrue($result3->isSuccess());

        // Verify each asset is locked by its owner
        $this->assertTrue($this->thereIsALockedAssetWith($assetId1, $owner1));
        $this->assertTrue($this->thereIsALockedAssetWith($assetId2, $owner2));
        $this->assertTrue($this->thereIsALockedAssetWith($assetId3, $owner3));

        // Unlock asset 2
        $result = $this->service->unlock($assetId2, $owner2, new DateTimeImmutable());
        $this->assertTrue($result->isSuccess());

        // Verify state
        $this->assertTrue($this->thereIsALockedAssetWith($assetId1, $owner1));
        $this->assertTrue($this->thereIsAnUnlockedAssetWith($assetId2));
        $this->assertTrue($this->thereIsALockedAssetWith($assetId3, $owner3));
    }

    public function testOwnerCannotUnlockAssetLockedByAnotherOwner(): void
    {
        // given
        $owner1 = OwnerId::of('OWNER_1');
        $owner2 = OwnerId::of('OWNER_2');
        $asset = $this->assetLockedBy($owner1);

        // when - owner2 tries to unlock
        $result = $this->service->unlock($asset->id(), $owner2, new DateTimeImmutable());

        // then
        $this->assertTrue($result->isFailure());
        $this->assertTrue($this->thereIsALockedAssetWith($asset->id(), $owner1));
    }

    public function testOverdueLockHandling(): void
    {
        // given - asset locked by owner
        $ownerId = OwnerId::of('OWNER_1');
        $asset = $this->assetLockedBy($ownerId);

        // when - process overdue (simulates time passing)
        $this->service->unlockIfOverdue($asset->id());

        // then - asset should be unlocked
        $this->assertTrue($this->thereIsAnUnlockedAssetWith($asset->id()));
    }
}
