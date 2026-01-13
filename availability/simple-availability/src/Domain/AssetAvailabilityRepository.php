<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Domain;

interface AssetAvailabilityRepository
{
    public function save(AssetAvailability $assetAvailability): void;

    public function findById(AssetId $assetId): ?AssetAvailability;

    public function existsById(AssetId $assetId): bool;
}
