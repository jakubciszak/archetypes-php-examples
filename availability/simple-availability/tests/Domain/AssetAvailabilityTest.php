<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Tests\Domain;

use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Availability\SimpleAvailability\Common\SystemClock;
use SoftwareArchetypes\Availability\SimpleAvailability\Domain\AssetAvailability;
use SoftwareArchetypes\Availability\SimpleAvailability\Domain\AssetId;
use SoftwareArchetypes\Availability\SimpleAvailability\Domain\MaintenanceLock;
use SoftwareArchetypes\Availability\SimpleAvailability\Domain\OwnerId;
use SoftwareArchetypes\Availability\SimpleAvailability\Domain\OwnerLock;
use SoftwareArchetypes\Availability\SimpleAvailability\Domain\WithdrawalLock;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\AssetActivated;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\AssetActivationRejected;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\AssetLocked;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\AssetLockRejected;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\AssetUnlocked;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\AssetUnlockingRejected;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\AssetWithdrawn;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\AssetWithdrawalRejected;

class AssetAvailabilityTest extends TestCase
{
    private AssetId $assetId;
    private OwnerId $ownerId;

    protected function setUp(): void
    {
        $this->assetId = AssetId::of('asset-123');
        $this->ownerId = OwnerId::of('owner-456');
    }

    public function testNewAssetHasMaintenanceLock(): void
    {
        $asset = AssetAvailability::of($this->assetId, new SystemClock());

        $lock = $asset->currentLock();
        $this->assertInstanceOf(MaintenanceLock::class, $lock);
    }

    public function testCanActivateAssetWithMaintenanceLock(): void
    {
        $asset = AssetAvailability::of($this->assetId, new SystemClock());

        $result = $asset->activate();

        $this->assertTrue($result->isSuccess());
        $this->assertInstanceOf(AssetActivated::class, $result->getSuccess());
        $this->assertNull($asset->currentLock());
    }

    public function testCannotActivateAlreadyActivatedAsset(): void
    {
        $asset = AssetAvailability::of($this->assetId, new SystemClock());
        $asset->activate();

        $result = $asset->activate();

        $this->assertTrue($result->isFailure());
        $this->assertInstanceOf(AssetActivationRejected::class, $result->getFailure());
    }

    public function testCanWithdrawAvailableAsset(): void
    {
        $asset = AssetAvailability::of($this->assetId, new SystemClock());
        $asset->activate();

        $result = $asset->withdraw();

        $this->assertTrue($result->isSuccess());
        $this->assertInstanceOf(AssetWithdrawn::class, $result->getSuccess());
        $this->assertInstanceOf(WithdrawalLock::class, $asset->currentLock());
    }

    public function testCannotWithdrawLockedAsset(): void
    {
        $asset = AssetAvailability::of($this->assetId, new SystemClock());
        $asset->activate();
        $asset->lockFor($this->ownerId, new DateInterval('PT30M'));

        $result = $asset->withdraw();

        $this->assertTrue($result->isFailure());
        $this->assertInstanceOf(AssetWithdrawalRejected::class, $result->getFailure());
    }

    public function testCanLockAvailableAsset(): void
    {
        $asset = AssetAvailability::of($this->assetId, new SystemClock());
        $asset->activate();

        $result = $asset->lockFor($this->ownerId, new DateInterval('PT30M'));

        $this->assertTrue($result->isSuccess());
        $this->assertInstanceOf(AssetLocked::class, $result->getSuccess());
        $this->assertInstanceOf(OwnerLock::class, $asset->currentLock());
    }

    public function testCannotLockAlreadyLockedAsset(): void
    {
        $asset = AssetAvailability::of($this->assetId, new SystemClock());
        $asset->activate();
        $asset->lockFor($this->ownerId, new DateInterval('PT30M'));

        $anotherOwner = OwnerId::of('another-owner');
        $result = $asset->lockFor($anotherOwner, new DateInterval('PT30M'));

        $this->assertTrue($result->isFailure());
        $this->assertInstanceOf(AssetLockRejected::class, $result->getFailure());
    }

    public function testCanLockIndefinitelyForExistingOwner(): void
    {
        $asset = AssetAvailability::of($this->assetId, new SystemClock());
        $asset->activate();
        $asset->lockFor($this->ownerId, new DateInterval('PT30M'));

        $result = $asset->lockIndefinitelyFor($this->ownerId);

        $this->assertTrue($result->isSuccess());
        $this->assertInstanceOf(AssetLocked::class, $result->getSuccess());
    }

    public function testCannotLockIndefinitelyWithoutExistingLock(): void
    {
        $asset = AssetAvailability::of($this->assetId, new SystemClock());
        $asset->activate();

        $result = $asset->lockIndefinitelyFor($this->ownerId);

        $this->assertTrue($result->isFailure());
        $this->assertInstanceOf(AssetLockRejected::class, $result->getFailure());
    }

    public function testCanUnlockAssetByOwner(): void
    {
        $asset = AssetAvailability::of($this->assetId, new SystemClock());
        $asset->activate();
        $asset->lockFor($this->ownerId, new DateInterval('PT30M'));

        $result = $asset->unlockFor($this->ownerId, new DateTimeImmutable());

        $this->assertTrue($result->isSuccess());
        $this->assertInstanceOf(AssetUnlocked::class, $result->getSuccess());
        $this->assertNull($asset->currentLock());
    }

    public function testCannotUnlockAssetByDifferentOwner(): void
    {
        $asset = AssetAvailability::of($this->assetId, new SystemClock());
        $asset->activate();
        $asset->lockFor($this->ownerId, new DateInterval('PT30M'));

        $anotherOwner = OwnerId::of('another-owner');
        $result = $asset->unlockFor($anotherOwner, new DateTimeImmutable());

        $this->assertTrue($result->isFailure());
        $this->assertInstanceOf(AssetUnlockingRejected::class, $result->getFailure());
    }

    public function testUnlockIfOverdueRemovesOwnerLock(): void
    {
        $asset = AssetAvailability::of($this->assetId, new SystemClock());
        $asset->activate();
        $asset->lockFor($this->ownerId, new DateInterval('PT30M'));

        $event = $asset->unlockIfOverdue();

        $this->assertNotNull($event);
        $this->assertNull($asset->currentLock());
    }

    public function testUnlockIfOverdueReturnsNullWhenNoOwnerLock(): void
    {
        $asset = AssetAvailability::of($this->assetId, new SystemClock());
        $asset->activate();

        $event = $asset->unlockIfOverdue();

        $this->assertNull($event);
    }
}
