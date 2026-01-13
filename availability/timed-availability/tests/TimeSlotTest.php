<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability\Tests;

use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Availability\TimedAvailability\TimeSlot;

final class TimeSlotTest extends TestCase
{
    public function testCanCreateTimeSlot(): void
    {
        $from = new DateTimeImmutable('2024-01-15 10:00:00');
        $to = new DateTimeImmutable('2024-01-15 12:00:00');

        $slot = new TimeSlot($from, $to);

        self::assertEquals($from, $slot->from());
        self::assertEquals($to, $slot->to());
    }

    public function testCanCreateEmptyTimeSlot(): void
    {
        $empty = TimeSlot::empty();

        self::assertTrue($empty->isEmpty());
        self::assertEquals(new DateTimeImmutable('@0'), $empty->from());
        self::assertEquals(new DateTimeImmutable('@0'), $empty->to());
    }

    public function testCanCreateDailyTimeSlotAtUTC(): void
    {
        $slot = TimeSlot::createDailyTimeSlotAtUTC(2024, 1, 15);

        $expectedFrom = new DateTimeImmutable('2024-01-15 00:00:00 UTC');
        $expectedTo = new DateTimeImmutable('2024-01-16 00:00:00 UTC');

        self::assertEquals($expectedFrom, $slot->from());
        self::assertEquals($expectedTo, $slot->to());
    }

    public function testCanCreateMonthlyTimeSlotAtUTC(): void
    {
        $slot = TimeSlot::createMonthlyTimeSlotAtUTC(2024, 1);

        $expectedFrom = new DateTimeImmutable('2024-01-01 00:00:00 UTC');
        $expectedTo = new DateTimeImmutable('2024-02-01 00:00:00 UTC');

        self::assertEquals($expectedFrom, $slot->from());
        self::assertEquals($expectedTo, $slot->to());
    }

    public function testDetectsOverlappingSlots(): void
    {
        $slot1 = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 12:00:00')
        );
        $slot2 = new TimeSlot(
            new DateTimeImmutable('2024-01-15 11:00:00'),
            new DateTimeImmutable('2024-01-15 13:00:00')
        );

        self::assertTrue($slot1->overlapsWith($slot2));
        self::assertTrue($slot2->overlapsWith($slot1));
    }

    public function testDetectsNonOverlappingSlots(): void
    {
        $slot1 = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 12:00:00')
        );
        $slot2 = new TimeSlot(
            new DateTimeImmutable('2024-01-15 13:00:00'),
            new DateTimeImmutable('2024-01-15 15:00:00')
        );

        self::assertFalse($slot1->overlapsWith($slot2));
        self::assertFalse($slot2->overlapsWith($slot1));
    }

    public function testDetectsAdjacentSlots(): void
    {
        $slot1 = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 12:00:00')
        );
        $slot2 = new TimeSlot(
            new DateTimeImmutable('2024-01-15 12:00:00'),
            new DateTimeImmutable('2024-01-15 14:00:00')
        );

        self::assertFalse($slot1->overlapsWith($slot2));
        self::assertFalse($slot2->overlapsWith($slot1));
    }

    public function testSlotIsWithinAnotherSlot(): void
    {
        $outer = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 14:00:00')
        );
        $inner = new TimeSlot(
            new DateTimeImmutable('2024-01-15 11:00:00'),
            new DateTimeImmutable('2024-01-15 13:00:00')
        );

        self::assertTrue($inner->within($outer));
        self::assertFalse($outer->within($inner));
    }

    public function testCalculatesCommonPartOfOverlappingSlots(): void
    {
        $slot1 = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 12:00:00')
        );
        $slot2 = new TimeSlot(
            new DateTimeImmutable('2024-01-15 11:00:00'),
            new DateTimeImmutable('2024-01-15 13:00:00')
        );

        $common = $slot1->commonPartWith($slot2);

        self::assertEquals(new DateTimeImmutable('2024-01-15 11:00:00'), $common->from());
        self::assertEquals(new DateTimeImmutable('2024-01-15 12:00:00'), $common->to());
    }

    public function testReturnsEmptyForNonOverlappingSlots(): void
    {
        $slot1 = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 12:00:00')
        );
        $slot2 = new TimeSlot(
            new DateTimeImmutable('2024-01-15 13:00:00'),
            new DateTimeImmutable('2024-01-15 15:00:00')
        );

        $common = $slot1->commonPartWith($slot2);

        self::assertTrue($common->isEmpty());
    }

    public function testCalculatesLeftoverAfterRemovingCommon(): void
    {
        $slot1 = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 12:00:00')
        );
        $slot2 = new TimeSlot(
            new DateTimeImmutable('2024-01-15 11:00:00'),
            new DateTimeImmutable('2024-01-15 13:00:00')
        );

        $leftover = $slot1->leftoverAfterRemovingCommonWith($slot2);

        self::assertCount(2, $leftover);
        self::assertEquals(new DateTimeImmutable('2024-01-15 10:00:00'), $leftover[0]->from());
        self::assertEquals(new DateTimeImmutable('2024-01-15 11:00:00'), $leftover[0]->to());
        self::assertEquals(new DateTimeImmutable('2024-01-15 12:00:00'), $leftover[1]->from());
        self::assertEquals(new DateTimeImmutable('2024-01-15 13:00:00'), $leftover[1]->to());
    }

    public function testReturnsEmptyLeftoverForEqualSlots(): void
    {
        $slot1 = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 12:00:00')
        );
        $slot2 = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 12:00:00')
        );

        $leftover = $slot1->leftoverAfterRemovingCommonWith($slot2);

        self::assertCount(0, $leftover);
    }

    public function testCalculatesDuration(): void
    {
        $slot = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 12:00:00')
        );

        $duration = $slot->duration();

        // 2 hours
        self::assertEquals(2, $duration->h);
        self::assertEquals(0, $duration->i);
    }

    public function testCanStretchSlot(): void
    {
        $slot = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 12:00:00')
        );

        $stretched = $slot->stretch(new DateInterval('PT1H'));

        self::assertEquals(new DateTimeImmutable('2024-01-15 09:00:00'), $stretched->from());
        self::assertEquals(new DateTimeImmutable('2024-01-15 13:00:00'), $stretched->to());
    }

    public function testEmptySlotIsEmpty(): void
    {
        $slot = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 10:00:00')
        );

        self::assertTrue($slot->isEmpty());
    }

    public function testNonEmptySlotIsNotEmpty(): void
    {
        $slot = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 12:00:00')
        );

        self::assertFalse($slot->isEmpty());
    }

    public function testEqualSlotsAreEqual(): void
    {
        $slot1 = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 12:00:00')
        );
        $slot2 = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 12:00:00')
        );

        self::assertTrue($slot1->equals($slot2));
    }

    public function testDifferentSlotsAreNotEqual(): void
    {
        $slot1 = new TimeSlot(
            new DateTimeImmutable('2024-01-15 10:00:00'),
            new DateTimeImmutable('2024-01-15 12:00:00')
        );
        $slot2 = new TimeSlot(
            new DateTimeImmutable('2024-01-15 11:00:00'),
            new DateTimeImmutable('2024-01-15 13:00:00')
        );

        self::assertFalse($slot1->equals($slot2));
    }
}
