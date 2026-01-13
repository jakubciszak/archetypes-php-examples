<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability;

interface ResourceAvailabilityRepository
{
    public function saveNew(ResourceGroupedAvailability $groupedAvailability): void;

    /**
     * @return list<ResourceAvailability>
     */
    public function loadAllWithinSlot(ResourceId $resourceId, TimeSlot $segment): array;

    /**
     * @return list<ResourceAvailability>
     */
    public function loadAllByParentIdWithinSlot(ResourceId $parentId, TimeSlot $segment): array;

    public function saveCheckingVersion(ResourceGroupedAvailability $groupedAvailability): bool;

    public function loadById(ResourceAvailabilityId $availabilityId): ?ResourceAvailability;

    /**
     * @param list<ResourceId> $resourceIds
     */
    public function loadAvailabilitiesOfRandomResourceWithin(array $resourceIds, TimeSlot $normalized): ResourceGroupedAvailability;
}
