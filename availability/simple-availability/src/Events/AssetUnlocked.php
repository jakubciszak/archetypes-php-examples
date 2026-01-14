<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Events;

use DateTimeImmutable;
use SoftwareArchetypes\Availability\SimpleAvailability\Domain\AssetId;
use SoftwareArchetypes\Availability\SimpleAvailability\Domain\OwnerId;

final readonly class AssetUnlocked extends BaseDomainEvent
{
    public const string TYPE = 'ASSET_UNLOCKED';

    private function __construct(
        string $id,
        DateTimeImmutable $occurredAt,
        private AssetId $assetId,
        private OwnerId $ownerId
    ) {
        parent::__construct($id, $occurredAt);
    }

    public static function from(AssetId $assetId, OwnerId $ownerId): self
    {
        return new self(
            self::generateId(),
            self::now(),
            $assetId,
            $ownerId
        );
    }

    public function getAssetId(): AssetId
    {
        return $this->assetId;
    }

    public function getOwnerId(): OwnerId
    {
        return $this->ownerId;
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}
