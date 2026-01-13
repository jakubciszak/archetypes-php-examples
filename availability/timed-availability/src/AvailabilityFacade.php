<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability;

use SoftwareArchetypes\Availability\TimedAvailability\Segment\SegmentInMinutes;
use SoftwareArchetypes\Availability\TimedAvailability\Segment\Segments;

final readonly class AvailabilityFacade
{
    public function __construct(
        private ResourceAvailabilityRepository $availabilityRepository,
        private ResourceAvailabilityReadModel $availabilityReadModel,
        private EventsPublisher $eventsPublisher,
        private Clock $clock
    ) {
    }

    public function createResourceSlots(ResourceId $resourceId, TimeSlot $timeslot): void
    {
        $groupedAvailability = ResourceGroupedAvailability::of($resourceId, $timeslot);
        $this->availabilityRepository->saveNew($groupedAvailability);
    }

    public function createResourceSlotsWithParent(
        ResourceId $resourceId,
        ResourceId $parentId,
        TimeSlot $timeslot
    ): void {
        $groupedAvailability = ResourceGroupedAvailability::ofWithParent($resourceId, $timeslot, $parentId);
        $this->availabilityRepository->saveNew($groupedAvailability);
    }

    public function block(ResourceId $resourceId, TimeSlot $timeSlot, Owner $requester): bool
    {
        $toBlock = $this->findGrouped($resourceId, $timeSlot);
        return $this->blockGrouped($requester, $toBlock);
    }

    public function release(ResourceId $resourceId, TimeSlot $timeSlot, Owner $requester): bool
    {
        $toRelease = $this->findGrouped($resourceId, $timeSlot);

        if ($toRelease->hasNoSlots()) {
            return false;
        }

        $result = $toRelease->release($requester);

        if ($result) {
            return $this->availabilityRepository->saveCheckingVersion($toRelease);
        }

        return $result;
    }

    public function disable(ResourceId $resourceId, TimeSlot $timeSlot, Owner $requester): bool
    {
        $toDisable = $this->findGrouped($resourceId, $timeSlot);

        if ($toDisable->hasNoSlots()) {
            return false;
        }

        $previousOwners = $toDisable->owners();
        $result = $toDisable->disable($requester);

        if ($result) {
            $result = $this->availabilityRepository->saveCheckingVersion($toDisable);

            if ($result) {
                $event = new ResourceTakenOver(
                    \Ramsey\Uuid\Uuid::uuid4(),
                    $resourceId,
                    $previousOwners,
                    $timeSlot,
                    $this->clock->now()
                );
                $this->eventsPublisher->publish($event);
            }
        }

        return $result;
    }

    /**
     * @param list<ResourceId> $resourceIds
     */
    public function blockRandomAvailable(array $resourceIds, TimeSlot $within, Owner $owner): ?ResourceId
    {
        $normalized = Segments::normalizeToSegmentBoundaries($within, SegmentInMinutes::defaultSegment());
        $groupedAvailability = $this->availabilityRepository->loadAvailabilitiesOfRandomResourceWithin(
            $resourceIds,
            $normalized
        );

        if ($this->blockGrouped($owner, $groupedAvailability)) {
            return $groupedAvailability->resourceId();
        }

        return null;
    }

    public function findGrouped(ResourceId $resourceId, TimeSlot $within): ResourceGroupedAvailability
    {
        $normalized = Segments::normalizeToSegmentBoundaries($within, SegmentInMinutes::defaultSegment());
        $availabilities = $this->availabilityRepository->loadAllWithinSlot($resourceId, $normalized);
        return new ResourceGroupedAvailability($availabilities);
    }

    public function findByParentId(ResourceId $parentId, TimeSlot $within): ResourceGroupedAvailability
    {
        $normalized = Segments::normalizeToSegmentBoundaries($within, SegmentInMinutes::defaultSegment());
        $availabilities = $this->availabilityRepository->loadAllByParentIdWithinSlot($parentId, $normalized);
        return new ResourceGroupedAvailability($availabilities);
    }

    public function loadCalendar(ResourceId $resourceId, TimeSlot $within): Calendar
    {
        $normalized = Segments::normalizeToSegmentBoundaries($within, SegmentInMinutes::defaultSegment());
        return $this->availabilityReadModel->load($resourceId, $normalized);
    }

    /**
     * @param list<ResourceId> $resources
     */
    public function loadCalendars(array $resources, TimeSlot $within): Calendars
    {
        $normalized = Segments::normalizeToSegmentBoundaries($within, SegmentInMinutes::defaultSegment());
        return $this->availabilityReadModel->loadAll($resources, $normalized);
    }

    private function blockGrouped(Owner $requester, ResourceGroupedAvailability $toBlock): bool
    {
        if ($toBlock->hasNoSlots()) {
            return false;
        }

        $result = $toBlock->block($requester);

        if ($result) {
            return $this->availabilityRepository->saveCheckingVersion($toBlock);
        }

        return $result;
    }
}
