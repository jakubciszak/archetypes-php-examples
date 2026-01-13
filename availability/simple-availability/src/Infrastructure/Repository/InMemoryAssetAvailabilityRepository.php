<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Infrastructure\Repository;

use SoftwareArchetypes\Availability\SimpleAvailability\Domain\AssetAvailability;
use SoftwareArchetypes\Availability\SimpleAvailability\Domain\AssetAvailabilityRepository;
use SoftwareArchetypes\Availability\SimpleAvailability\Domain\AssetId;

class InMemoryAssetAvailabilityRepository implements AssetAvailabilityRepository
{
    /**
     * @var array<string, AssetAvailability>
     */
    private array $storage = [];

    public function save(AssetAvailability $assetAvailability): void
    {
        $this->storage[$assetAvailability->id()->asString()] = $assetAvailability;
    }

    public function findById(AssetId $assetId): ?AssetAvailability
    {
        return $this->storage[$assetId->asString()] ?? null;
    }

    public function existsById(AssetId $assetId): bool
    {
        return isset($this->storage[$assetId->asString()]);
    }

    public function clear(): void
    {
        $this->storage = [];
    }

    /**
     * @return array<AssetAvailability>
     */
    public function findAll(): array
    {
        return array_values($this->storage);
    }
}
