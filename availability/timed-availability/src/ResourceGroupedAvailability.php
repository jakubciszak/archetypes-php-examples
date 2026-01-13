<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability;

use SoftwareArchetypes\Availability\TimedAvailability\Segment\SegmentInMinutes;
use SoftwareArchetypes\Availability\TimedAvailability\Segment\Segments;

final readonly class ResourceGroupedAvailability
{
    /**
     * @param list<ResourceAvailability> $resourceAvailabilities
     */
    public function __construct(private array $resourceAvailabilities)
    {
    }

    public static function of(ResourceId $resourceId, TimeSlot $timeslot): self
    {
        $resourceAvailabilities = array_map(
            fn(TimeSlot $segment) => new ResourceAvailability(
                ResourceAvailabilityId::newOne(),
                $resourceId,
                $segment
            ),
            Segments::split($timeslot, SegmentInMinutes::defaultSegment())
        );

        return new self($resourceAvailabilities);
    }

    public static function ofWithParent(ResourceId $resourceId, TimeSlot $timeslot, ResourceId $parentId): self
    {
        $resourceAvailabilities = array_map(
            fn(TimeSlot $segment) => new ResourceAvailability(
                ResourceAvailabilityId::newOne(),
                $resourceId,
                $parentId,
                $segment
            ),
            Segments::split($timeslot, SegmentInMinutes::defaultSegment())
        );

        return new self($resourceAvailabilities);
    }

    public function block(Owner $requester): bool
    {
        foreach ($this->resourceAvailabilities as $resourceAvailability) {
            if (!$resourceAvailability->block($requester)) {
                return false;
            }
        }

        return true;
    }

    public function disable(Owner $requester): bool
    {
        foreach ($this->resourceAvailabilities as $resourceAvailability) {
            if (!$resourceAvailability->disable($requester)) {
                return false;
            }
        }

        return true;
    }

    public function release(Owner $requester): bool
    {
        foreach ($this->resourceAvailabilities as $resourceAvailability) {
            if (!$resourceAvailability->release($requester)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return list<ResourceAvailability>
     */
    public function availabilities(): array
    {
        return $this->resourceAvailabilities;
    }

    public function resourceId(): ?ResourceId
    {
        if (empty($this->resourceAvailabilities)) {
            return null;
        }

        return $this->resourceAvailabilities[0]->resourceId();
    }

    public function size(): int
    {
        return count($this->resourceAvailabilities);
    }

    public function blockedEntirelyBy(Owner $owner): bool
    {
        foreach ($this->resourceAvailabilities as $ra) {
            if (!$ra->blockedBy()->equals($owner)) {
                return false;
            }
        }

        return true;
    }

    public function isDisabledEntirelyBy(Owner $owner): bool
    {
        foreach ($this->resourceAvailabilities as $ra) {
            if (!$ra->isDisabledBy($owner)) {
                return false;
            }
        }

        return true;
    }

    public function isEntirelyWithParentId(ResourceId $parentId): bool
    {
        foreach ($this->resourceAvailabilities as $ra) {
            if (!$ra->resourceParentId()->equals($parentId)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return list<ResourceAvailability>
     */
    public function findBlockedBy(Owner $owner): array
    {
        return array_values(array_filter(
            $this->resourceAvailabilities,
            fn(ResourceAvailability $ra) => $ra->blockedBy()->equals($owner)
        ));
    }

    public function isEntirelyAvailable(): bool
    {
        foreach ($this->resourceAvailabilities as $ra) {
            if (!$ra->blockedBy()->byNone()) {
                return false;
            }
        }

        return true;
    }

    public function hasNoSlots(): bool
    {
        return empty($this->resourceAvailabilities);
    }

    /**
     * @return list<Owner>
     */
    public function owners(): array
    {
        $owners = [];
        $seen = [];

        foreach ($this->resourceAvailabilities as $ra) {
            $owner = $ra->blockedBy();
            $id = $owner->id();
            $key = $id?->toString() ?? 'none';

            if (!isset($seen[$key])) {
                $owners[] = $owner;
                $seen[$key] = true;
            }
        }

        return $owners;
    }
}
