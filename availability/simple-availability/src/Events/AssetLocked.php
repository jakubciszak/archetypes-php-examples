<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Events;

use DateTimeImmutable;
use SoftwareArchetypes\Availability\SimpleAvailability\Domain\AssetId;
use SoftwareArchetypes\Availability\SimpleAvailability\Domain\OwnerId;

final readonly class AssetLocked extends BaseDomainEvent
{
    public const string TYPE = 'ASSET_LOCKED';

    private function __construct(
        string $id,
        DateTimeImmutable $occurredAt,
        private AssetId $assetId,
        private OwnerId $ownerId,
        private DateTimeImmutable $from,
        private DateTimeImmutable $to
    ) {
        parent::__construct($id, $occurredAt);
    }

    public static function from(
        AssetId $assetId,
        OwnerId $ownerId,
        DateTimeImmutable $from,
        DateTimeImmutable $to
    ): self {
        return new self(
            self::generateId(),
            self::now(),
            $assetId,
            $ownerId,
            $from,
            $to
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

    public function getFrom(): DateTimeImmutable
    {
        return $this->from;
    }

    public function getTo(): DateTimeImmutable
    {
        return $this->to;
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}
