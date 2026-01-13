<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Domain;

use DateInterval;
use DateTimeImmutable;
use SoftwareArchetypes\Availability\SimpleAvailability\Common\Clock;
use SoftwareArchetypes\Availability\SimpleAvailability\Common\Result;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\AssetActivated;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\AssetActivationRejected;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\AssetLockExpired;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\AssetLockRejected;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\AssetLocked;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\AssetUnlocked;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\AssetUnlockingRejected;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\AssetWithdrawalRejected;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\AssetWithdrawn;

class AssetAvailability
{
    private const string ASSET_LOCKED_REASON = 'ASSET_CURRENTLY_LOCKED';
    private const string NO_LOCK_ON_THE_ASSET_REASON = 'NO_LOCK_ON_THE_ASSET';
    private const string NO_LOCK_DEFINED_FOR_OWNER_REASON = 'NO_LOCK_DEFINED_FOR_OWNER';
    private const string ASSET_ALREADY_ACTIVATED_REASON = 'ASSET_ALREADY_ACTIVATED';
    private const int INDEFINITE_LOCK_DAYS = 365;

    private ?Lock $currentLock;

    private function __construct(
        private readonly AssetId $assetId,
        private readonly Clock $clock
    ) {
        $this->currentLock = new MaintenanceLock();
    }

    public static function of(AssetId $assetId, Clock $clock): self
    {
        return new self($assetId, $clock);
    }

    /**
     * @return Result<AssetActivationRejected, AssetActivated>
     */
    public function activate(): Result
    {
        if ($this->currentLock instanceof MaintenanceLock) {
            $this->currentLock = null;
            return Result::success(AssetActivated::from($this->assetId));
        }
        return Result::failure(
            AssetActivationRejected::from($this->assetId, self::ASSET_ALREADY_ACTIVATED_REASON)
        );
    }

    /**
     * @return Result<AssetWithdrawalRejected, AssetWithdrawn>
     */
    public function withdraw(): Result
    {
        if ($this->currentLock === null || $this->currentLock instanceof MaintenanceLock) {
            $this->currentLock = new WithdrawalLock();
            return Result::success(AssetWithdrawn::from($this->assetId));
        }
        return Result::failure(
            AssetWithdrawalRejected::from($this->assetId, self::ASSET_LOCKED_REASON)
        );
    }

    /**
     * @return Result<AssetLockRejected, AssetLocked>
     */
    public function lockFor(OwnerId $ownerId, DateInterval $time): Result
    {
        if ($this->currentLock === null) {
            $now = $this->clock->now();
            $validUntil = $now->add($time);
            $this->currentLock = new OwnerLock($ownerId, $validUntil);
            return Result::success(
                AssetLocked::from($this->assetId, $ownerId, $now, $validUntil)
            );
        }
        return Result::failure(
            AssetLockRejected::from($this->assetId, $ownerId, self::ASSET_LOCKED_REASON)
        );
    }

    /**
     * @return Result<AssetLockRejected, AssetLocked>
     */
    public function lockIndefinitelyFor(OwnerId $ownerId): Result
    {
        if ($this->thereIsAnActiveLockFor($ownerId)) {
            $now = $this->clock->now();
            $validUntil = $now->add(new DateInterval('P' . self::INDEFINITE_LOCK_DAYS . 'D'));
            $this->currentLock = new OwnerLock($ownerId, $validUntil);
            return Result::success(
                AssetLocked::from($this->assetId, $ownerId, $now, $validUntil)
            );
        }
        return Result::failure(
            AssetLockRejected::from($this->assetId, $ownerId, self::NO_LOCK_DEFINED_FOR_OWNER_REASON)
        );
    }

    /**
     * @return Result<AssetUnlockingRejected, AssetUnlocked>
     */
    public function unlockFor(OwnerId $ownerId, DateTimeImmutable $at): Result
    {
        if ($this->thereIsAnActiveLockFor($ownerId)) {
            $this->currentLock = null;
            return Result::success(AssetUnlocked::from($this->assetId, $ownerId));
        }
        return Result::failure(
            AssetUnlockingRejected::from($this->assetId, $ownerId, self::NO_LOCK_ON_THE_ASSET_REASON)
        );
    }

    public function unlockIfOverdue(): ?AssetLockExpired
    {
        if ($this->currentLock instanceof OwnerLock) {
            $ownerId = $this->currentLock->ownerId();
            $this->currentLock = null;
            return AssetLockExpired::from($this->assetId, $ownerId);
        }
        return null;
    }

    public function id(): AssetId
    {
        return $this->assetId;
    }

    public function currentLock(): ?Lock
    {
        return $this->currentLock;
    }

    private function thereIsAnActiveLockFor(OwnerId $ownerId): bool
    {
        return $this->currentLock !== null && $this->currentLock->wasMadeFor($ownerId);
    }

    public function with(Lock $lock): self
    {
        $this->currentLock = $lock;
        return $this;
    }
}
