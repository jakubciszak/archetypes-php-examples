<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability\Tests\Segment;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Availability\TimedAvailability\Domain\TimeSlot;
use SoftwareArchetypes\Availability\TimedAvailability\Segment\SegmentInMinutes;
use SoftwareArchetypes\Availability\TimedAvailability\Segment\Segments;

final class SegmentsTest extends TestCase
{
    public function testCanNormalizeSlotToSegmentBoundaries(): void
    {
        // Request from 10:05 to 11:30 with 60-minute segments
        // Should normalize to 10:00 to 12:00
        $slot = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:05:00'),
            new DateTimeImmutable('2024-01-15 11:30:00')
        );

        $normalized = Segments::normalizeToSegmentBoundaries($slot, SegmentInMinutes::of(60));

        self::assertEquals(new DateTimeImmutable('2024-01-15 10:00:00'), $normalized->from());
        self::assertEquals(new DateTimeImmutable('2024-01-15 12:00:00'), $normalized->to());
    }

    public function testCanNormalizeSlotAlreadyAlignedToSegmentBoundaries(): void
    {
        // Request from 10:00 to 12:00 with 60-minute segments
        // Should remain 10:00 to 12:00
        $slot = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 12:00:00')
        );

        $normalized = Segments::normalizeToSegmentBoundaries($slot, SegmentInMinutes::of(60));

        self::assertEquals(new DateTimeImmutable('2024-01-15 10:00:00'), $normalized->from());
        self::assertEquals(new DateTimeImmutable('2024-01-15 12:00:00'), $normalized->to());
    }

    public function testCanSplitSlotIntoSegments(): void
    {
        // 4-hour slot should split into four 60-minute segments
        $slot = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 14:00:00')
        );

        $segments = Segments::split($slot, SegmentInMinutes::of(60));

        self::assertCount(4, $segments);
        self::assertEquals(new DateTimeImmutable('2024-01-15 10:00:00'), $segments[0]->from());
        self::assertEquals(new DateTimeImmutable('2024-01-15 11:00:00'), $segments[0]->to());
        self::assertEquals(new DateTimeImmutable('2024-01-15 11:00:00'), $segments[1]->from());
        self::assertEquals(new DateTimeImmutable('2024-01-15 12:00:00'), $segments[1]->to());
        self::assertEquals(new DateTimeImmutable('2024-01-15 12:00:00'), $segments[2]->from());
        self::assertEquals(new DateTimeImmutable('2024-01-15 13:00:00'), $segments[2]->to());
        self::assertEquals(new DateTimeImmutable('2024-01-15 13:00:00'), $segments[3]->from());
        self::assertEquals(new DateTimeImmutable('2024-01-15 14:00:00'), $segments[3]->to());
    }

    public function testCanSplitNonAlignedSlotIntoSegments(): void
    {
        // Slot from 10:05 to 11:30 should normalize to 10:00-12:00 and split into two segments
        $slot = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:05:00'),
            new DateTimeImmutable('2024-01-15 11:30:00')
        );

        $segments = Segments::split($slot, SegmentInMinutes::of(60));

        self::assertCount(2, $segments);
        self::assertEquals(new DateTimeImmutable('2024-01-15 10:00:00'), $segments[0]->from());
        self::assertEquals(new DateTimeImmutable('2024-01-15 11:00:00'), $segments[0]->to());
        self::assertEquals(new DateTimeImmutable('2024-01-15 11:00:00'), $segments[1]->from());
        self::assertEquals(new DateTimeImmutable('2024-01-15 12:00:00'), $segments[1]->to());
    }

    public function testCanSplitWithDifferentSegmentSize(): void
    {
        // 2-hour slot should split into four 30-minute segments
        $slot = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 12:00:00')
        );

        $segments = Segments::split($slot, SegmentInMinutes::of(30));

        self::assertCount(4, $segments);
        self::assertEquals(new DateTimeImmutable('2024-01-15 10:00:00'), $segments[0]->from());
        self::assertEquals(new DateTimeImmutable('2024-01-15 10:30:00'), $segments[0]->to());
        self::assertEquals(new DateTimeImmutable('2024-01-15 11:30:00'), $segments[3]->from());
        self::assertEquals(new DateTimeImmutable('2024-01-15 12:00:00'), $segments[3]->to());
    }

    public function testMinimalSlotBecomesOneSegment(): void
    {
        // 30-minute slot with 60-minute segment size should become one 60-minute segment
        $slot = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:15:00'),
            new DateTimeImmutable('2024-01-15 10:45:00')
        );

        $segments = Segments::split($slot, SegmentInMinutes::of(60));

        self::assertCount(1, $segments);
        self::assertEquals(new DateTimeImmutable('2024-01-15 10:00:00'), $segments[0]->from());
        self::assertEquals(new DateTimeImmutable('2024-01-15 11:00:00'), $segments[0]->to());
    }

    public function testDefaultSegmentIs60Minutes(): void
    {
        self::assertEquals(60, Segments::DEFAULT_SEGMENT_DURATION_IN_MINUTES);
    }
}
