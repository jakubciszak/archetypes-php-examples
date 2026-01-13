<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability\Segment;

use DateInterval;
use DateTimeImmutable;
use SoftwareArchetypes\Availability\TimedAvailability\TimeSlot;

final readonly class SlotToNormalizedSlot
{
    public function apply(TimeSlot $timeSlot, SegmentInMinutes $segmentInMinutes): TimeSlot
    {
        $segmentDuration = $segmentInMinutes->value;
        $segmentStart = $this->normalizeStart($timeSlot->from(), $segmentDuration);
        $segmentEnd = $this->normalizeEnd($timeSlot->to(), $segmentDuration);

        $normalized = new TimeSlot($segmentStart, $segmentEnd);
        $minimalSegment = new TimeSlot(
            $segmentStart,
            $segmentStart->add(new DateInterval(sprintf('PT%dM', $segmentInMinutes->value)))
        );

        if ($normalized->within($minimalSegment)) {
            return $minimalSegment;
        }

        return $normalized;
    }

    private function normalizeStart(DateTimeImmutable $initialStart, int $segmentDuration): DateTimeImmutable
    {
        $closestSegmentStart = $initialStart->setTime(
            (int) $initialStart->format('H'),
            0,
            0,
            0
        );

        $interval = new DateInterval(sprintf('PT%dM', $segmentDuration));

        if ($closestSegmentStart->add($interval) > $initialStart) {
            return $closestSegmentStart;
        }

        while ($closestSegmentStart < $initialStart) {
            $closestSegmentStart = $closestSegmentStart->add($interval);
        }

        return $closestSegmentStart;
    }

    private function normalizeEnd(DateTimeImmutable $initialEnd, int $segmentDuration): DateTimeImmutable
    {
        $closestSegmentEnd = $initialEnd->setTime(
            (int) $initialEnd->format('H'),
            0,
            0,
            0
        );

        $interval = new DateInterval(sprintf('PT%dM', $segmentDuration));

        while ($initialEnd > $closestSegmentEnd) {
            $closestSegmentEnd = $closestSegmentEnd->add($interval);
        }

        return $closestSegmentEnd;
    }
}
