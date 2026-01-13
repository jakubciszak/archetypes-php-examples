<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Tests\Support;

use SoftwareArchetypes\Availability\SimpleAvailability\Domain\AssetId;
use SoftwareArchetypes\Availability\SimpleAvailability\Domain\OwnerId;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\AssetLocked;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\AssetRegistered;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\AssetUnlocked;
use SoftwareArchetypes\Availability\SimpleAvailability\Events\AssetWithdrawn;
use SoftwareArchetypes\Availability\SimpleAvailability\Infrastructure\EventPublisher\InMemoryDomainEventsPublisher;

trait AssetAvailabilityEventsSupport
{
    protected InMemoryDomainEventsPublisher $eventPublisher;

    protected function setupEventPublisher(): void
    {
        $this->eventPublisher = new InMemoryDomainEventsPublisher();
    }

    protected function assetRegisteredEventWasPublishedFor(AssetId $assetId): bool
    {
        foreach ($this->eventPublisher->getEventsOfType(AssetRegistered::class) as $event) {
            if ($event->getAssetId()->equals($assetId)) {
                return true;
            }
        }
        return false;
    }

    protected function assetWithdrawnEventWasPublishedFor(AssetId $assetId): bool
    {
        foreach ($this->eventPublisher->getEventsOfType(AssetWithdrawn::class) as $event) {
            if ($event->getAssetId()->equals($assetId)) {
                return true;
            }
        }
        return false;
    }

    protected function assetLockedEventWasPublishedFor(AssetId $assetId, OwnerId $ownerId): bool
    {
        foreach ($this->eventPublisher->getEventsOfType(AssetLocked::class) as $event) {
            if ($event->getAssetId()->equals($assetId) && $event->getOwnerId()->equals($ownerId)) {
                return true;
            }
        }
        return false;
    }

    protected function assetUnlockedEventWasPublishedFor(AssetId $assetId, OwnerId $ownerId): bool
    {
        foreach ($this->eventPublisher->getEventsOfType(AssetUnlocked::class) as $event) {
            if ($event->getAssetId()->equals($assetId) && $event->getOwnerId()->equals($ownerId)) {
                return true;
            }
        }
        return false;
    }
}
