<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Tests\Fixtures;

use DateInterval;
use SoftwareArchetypes\Availability\SimpleAvailability\Common\Clock;
use SoftwareArchetypes\Availability\SimpleAvailability\Common\SystemClock;
use SoftwareArchetypes\Availability\SimpleAvailability\Domain\AssetAvailability;
use SoftwareArchetypes\Availability\SimpleAvailability\Domain\AssetId;
use SoftwareArchetypes\Availability\SimpleAvailability\Domain\OwnerId;

class AssetAvailabilityFixture
{
    private ?AssetId $assetId = null;
    private bool $shouldActivate = false;
    private ?OwnerId $lockOwnerId = null;
    private ?DateInterval $lockDuration = null;
    private ?Clock $clock = null;

    public static function create(?Clock $clock = null): self
    {
        $fixture = new self();
        $fixture->clock = $clock ?? new SystemClock();
        return $fixture;
    }

    public function withAssetId(AssetId $assetId): self
    {
        $this->assetId = $assetId;
        return $this;
    }

    public function thatIsActive(): self
    {
        $this->shouldActivate = true;
        return $this;
    }

    public function thatWasLockedByOwnerWith(OwnerId $ownerId): self
    {
        $this->lockOwnerId = $ownerId;
        return $this;
    }

    public function thatWasLockedBySomeOwner(): self
    {
        $this->lockOwnerId = OwnerId::of('SOME_OWNER_' . bin2hex(random_bytes(4)));
        return $this;
    }

    public function forSomeValidDuration(): self
    {
        $this->lockDuration = new DateInterval('PT30M');
        return $this;
    }

    public function forDuration(DateInterval $duration): self
    {
        $this->lockDuration = $duration;
        return $this;
    }

    public function get(): AssetAvailability
    {
        $assetId = $this->assetId ?? AssetId::of('asset-' . bin2hex(random_bytes(8)));
        $clock = $this->clock ?? new SystemClock();
        $asset = AssetAvailability::of($assetId, $clock);

        if ($this->shouldActivate) {
            $asset->activate();
        }

        if ($this->lockOwnerId !== null && $this->lockDuration !== null) {
            $asset->lockFor($this->lockOwnerId, $this->lockDuration);
        }

        return $asset;
    }

    public static function someAssetIdValue(): string
    {
        return 'asset-' . bin2hex(random_bytes(8));
    }

    public static function someValidDuration(): DateInterval
    {
        return new DateInterval('PT30M');
    }

    public static function someNewAsset(?Clock $clock = null): AssetAvailability
    {
        $clock = $clock ?? new SystemClock();
        return AssetAvailability::of(AssetId::of(self::someAssetIdValue()), $clock);
    }
}
