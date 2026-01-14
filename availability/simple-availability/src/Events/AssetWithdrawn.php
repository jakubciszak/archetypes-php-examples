<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Events;

use DateTimeImmutable;
use SoftwareArchetypes\Availability\SimpleAvailability\Domain\AssetId;

final readonly class AssetWithdrawn extends BaseDomainEvent
{
    public const string TYPE = 'ASSET_WITHDRAWN';

    private function __construct(
        string $id,
        DateTimeImmutable $occurredAt,
        private AssetId $assetId
    ) {
        parent::__construct($id, $occurredAt);
    }

    public static function from(AssetId $assetId): self
    {
        return new self(
            self::generateId(),
            self::now(),
            $assetId
        );
    }

    public function getAssetId(): AssetId
    {
        return $this->assetId;
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}
