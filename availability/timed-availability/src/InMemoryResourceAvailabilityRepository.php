<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability;

final class InMemoryResourceAvailabilityRepository implements
    ResourceAvailabilityRepository,
    ResourceAvailabilityReadModel
{
    /**
     * @var array<string, ResourceAvailability>
     */
    private array $storage = [];

    public function saveNew(ResourceGroupedAvailability $groupedAvailability): void
    {
        foreach ($groupedAvailability->availabilities() as $availability) {
            $key = $availability->id()->id()?->toString() ?? '';
            $this->storage[$key] = $availability;
        }
    }

    public function loadAllWithinSlot(ResourceId $resourceId, TimeSlot $segment): array
    {
        $result = [];

        foreach ($this->storage as $availability) {
            if (
                $availability->resourceId()->equals($resourceId) &&
                $availability->segment()->from() >= $segment->from() &&
                $availability->segment()->to() <= $segment->to()
            ) {
                $result[] = $availability;
            }
        }

        return $result;
    }

    public function loadAllByParentIdWithinSlot(ResourceId $parentId, TimeSlot $segment): array
    {
        $result = [];

        foreach ($this->storage as $availability) {
            if (
                $availability->resourceParentId()->equals($parentId) &&
                $availability->segment()->from() >= $segment->from() &&
                $availability->segment()->to() <= $segment->to()
            ) {
                $result[] = $availability;
            }
        }

        return $result;
    }

    public function saveCheckingVersion(ResourceGroupedAvailability $groupedAvailability): bool
    {
        foreach ($groupedAvailability->availabilities() as $availability) {
            $key = $availability->id()->id()?->toString() ?? '';

            if (!isset($this->storage[$key])) {
                return false;
            }

            $stored = $this->storage[$key];

            if ($stored->version() !== $availability->version()) {
                return false;
            }
        }

        // All versions match, update all
        foreach ($groupedAvailability->availabilities() as $availability) {
            $key = $availability->id()->id()?->toString() ?? '';
            $this->storage[$key] = $availability;
        }

        return true;
    }

    public function loadById(ResourceAvailabilityId $availabilityId): ?ResourceAvailability
    {
        $key = $availabilityId->id()?->toString() ?? '';
        return $this->storage[$key] ?? null;
    }

    public function loadAvailabilitiesOfRandomResourceWithin(
        array $resourceIds,
        TimeSlot $normalized
    ): ResourceGroupedAvailability {
        foreach ($resourceIds as $resourceId) {
            $availabilities = $this->loadAllWithinSlot($resourceId, $normalized);

            $allAvailable = true;
            foreach ($availabilities as $availability) {
                if (!$availability->blockedBy()->byNone()) {
                    $allAvailable = false;
                    break;
                }
            }

            if ($allAvailable && !empty($availabilities)) {
                return new ResourceGroupedAvailability($availabilities);
            }
        }

        return new ResourceGroupedAvailability([]);
    }

    public function load(ResourceId $resourceId, TimeSlot $timeSlot): Calendar
    {
        $availabilities = $this->loadAllWithinSlot($resourceId, $timeSlot);

        // Group time slots by owner
        /** @var array<string, list<TimeSlot>> $slotsByOwner */
        $slotsByOwner = [];

        foreach ($availabilities as $availability) {
            $owner = $availability->blockedBy();
            $key = $owner->byNone() ? 'none' : ($owner->id()?->toString() ?? 'none');

            if (!isset($slotsByOwner[$key])) {
                $slotsByOwner[$key] = [];
            }

            $slotsByOwner[$key][] = $availability->segment();
        }

        return new Calendar($resourceId, $slotsByOwner);
    }

    /**
     * @param list<ResourceId> $resourceIds
     */
    public function loadAll(array $resourceIds, TimeSlot $timeSlot): Calendars
    {
        $calendars = [];
        foreach ($resourceIds as $resourceId) {
            $calendars[] = $this->load($resourceId, $timeSlot);
        }
        return Calendars::of($calendars);
    }
}
