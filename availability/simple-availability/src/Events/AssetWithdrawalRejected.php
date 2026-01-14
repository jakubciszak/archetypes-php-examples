<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Events;

use DateTimeImmutable;
use SoftwareArchetypes\Availability\SimpleAvailability\Domain\AssetId;

final readonly class AssetWithdrawalRejected extends BaseDomainEvent
{
    public const string TYPE = 'ASSET_WITHDRAWAL_REJECTED';

    private function __construct(
        string $id,
        DateTimeImmutable $occurredAt,
        private AssetId $assetId,
        private string $reason
    ) {
        parent::__construct($id, $occurredAt);
    }

    public static function from(AssetId $assetId, string $reason): self
    {
        return new self(
            self::generateId(),
            self::now(),
            $assetId,
            $reason
        );
    }

    public function getAssetId(): AssetId
    {
        return $this->assetId;
    }

    public function getReason(): string
    {
        return $this->reason;
    }

    public function getType(): string
    {
        return self::TYPE;
    }
}
