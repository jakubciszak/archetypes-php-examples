<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability\Segment;

use InvalidArgumentException;

final readonly class SegmentInMinutes
{
    private function __construct(public int $value)
    {
    }

    public static function of(int $minutes): self
    {
        if ($minutes <= 0) {
            throw new InvalidArgumentException('SegmentInMinutes duration must be positive');
        }

        return new self($minutes);
    }

    public static function defaultSegment(): self
    {
        return self::of(Segments::DEFAULT_SEGMENT_DURATION_IN_MINUTES);
    }
}
