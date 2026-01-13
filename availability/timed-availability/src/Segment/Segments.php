<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability\Segment;

use SoftwareArchetypes\Availability\TimedAvailability\TimeSlot;

final class Segments
{
    public const int DEFAULT_SEGMENT_DURATION_IN_MINUTES = 60;

    /**
     * @return list<TimeSlot>
     */
    public static function split(TimeSlot $timeSlot, SegmentInMinutes $unit): array
    {
        $normalizedSlot = self::normalizeToSegmentBoundaries($timeSlot, $unit);
        return (new SlotToSegments())->apply($normalizedSlot, $unit);
    }

    public static function normalizeToSegmentBoundaries(TimeSlot $timeSlot, SegmentInMinutes $unit): TimeSlot
    {
        return (new SlotToNormalizedSlot())->apply($timeSlot, $unit);
    }
}
