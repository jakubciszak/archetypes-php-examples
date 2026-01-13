<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability;

interface ResourceAvailabilityReadModel
{
    public function load(ResourceId $resourceId, TimeSlot $timeSlot): Calendar;

    /**
     * @param list<ResourceId> $resourceIds
     */
    public function loadAll(array $resourceIds, TimeSlot $timeSlot): Calendars;
}
