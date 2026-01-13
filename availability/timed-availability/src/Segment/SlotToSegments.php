<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability\Segment;

use DateInterval;
use DateTimeImmutable;
use SoftwareArchetypes\Availability\TimedAvailability\Domain\TimeSlot;

final readonly class SlotToSegments
{
    /**
     * @return list<TimeSlot>
     */
    public function apply(TimeSlot $timeSlot, SegmentInMinutes $duration): array
    {
        $segmentInterval = new DateInterval(sprintf('PT%dM', $duration->value));
        $minimalSegment = new TimeSlot(
            $timeSlot->from(),
            $timeSlot->from()->add($segmentInterval)
        );

        if ($timeSlot->within($minimalSegment)) {
            return [$minimalSegment];
        }

        $segmentDuration = $duration->value;
        $numberOfSegments = $this->calculateNumberOfSegments($timeSlot, $segmentDuration);

        $segments = [];
        $currentStart = $timeSlot->from();

        for ($i = 0; $i < $numberOfSegments; $i++) {
            $currentEnd = $this->calculateEnd($segmentDuration, $currentStart, $timeSlot->to());
            $segments[] = new TimeSlot($currentStart, $currentEnd);
            $currentStart = $currentStart->add(new DateInterval(sprintf('PT%dM', $segmentDuration)));
        }

        return $segments;
    }

    private function calculateNumberOfSegments(TimeSlot $timeSlot, int $segmentDuration): int
    {
        $durationInMinutes = ($timeSlot->to()->getTimestamp() - $timeSlot->from()->getTimestamp()) / 60;
        return (int) ceil($durationInMinutes / $segmentDuration);
    }

    private function calculateEnd(int $segmentDuration, DateTimeImmutable $currentStart, DateTimeImmutable $initialEnd): DateTimeImmutable
    {
        $segmentEnd = $currentStart->add(new DateInterval(sprintf('PT%dM', $segmentDuration)));

        if ($initialEnd < $segmentEnd) {
            return $initialEnd;
        }

        return $segmentEnd;
    }
}
