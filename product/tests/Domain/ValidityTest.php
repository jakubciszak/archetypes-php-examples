<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Domain;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Product\Validity;

final class ValidityTest extends TestCase
{
    public function testCanBeCreatedWithFromAndToDate(): void
    {
        $from = new \DateTimeImmutable('2025-01-01');
        $to = new \DateTimeImmutable('2025-12-31');
        $validity = new Validity($from, $to);

        self::assertEquals($from, $validity->from());
        self::assertEquals($to, $validity->to());
    }

    public function testCanBeCreatedWithOnlyFromDate(): void
    {
        $from = new \DateTimeImmutable('2025-01-01');
        $validity = new Validity($from);

        self::assertEquals($from, $validity->from());
        self::assertNull($validity->to());
    }

    public function testRejectsToDateBeforeFromDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('To date cannot be before from date');

        $from = new \DateTimeImmutable('2025-12-31');
        $to = new \DateTimeImmutable('2025-01-01');

        new Validity($from, $to);
    }

    public function testAcceptsSameFromAndToDate(): void
    {
        $date = new \DateTimeImmutable('2025-06-15');
        $validity = new Validity($date, $date);

        self::assertEquals($date, $validity->from());
        self::assertEquals($date, $validity->to());
    }

    public function testCanCheckIfDateIsWithinValidity(): void
    {
        $from = new \DateTimeImmutable('2025-01-01');
        $to = new \DateTimeImmutable('2025-12-31');
        $validity = new Validity($from, $to);

        self::assertTrue($validity->isValidAt(new \DateTimeImmutable('2025-06-15')));
        self::assertTrue($validity->isValidAt(new \DateTimeImmutable('2025-01-01')));
        self::assertTrue($validity->isValidAt(new \DateTimeImmutable('2025-12-31')));
        self::assertFalse($validity->isValidAt(new \DateTimeImmutable('2024-12-31')));
        self::assertFalse($validity->isValidAt(new \DateTimeImmutable('2026-01-01')));
    }

    public function testOpenEndedValidityIsValidForAnyFutureDate(): void
    {
        $from = new \DateTimeImmutable('2025-01-01');
        $validity = new Validity($from);

        self::assertTrue($validity->isValidAt(new \DateTimeImmutable('2025-01-01')));
        self::assertTrue($validity->isValidAt(new \DateTimeImmutable('2030-01-01')));
        self::assertTrue($validity->isValidAt(new \DateTimeImmutable('2050-01-01')));
        self::assertFalse($validity->isValidAt(new \DateTimeImmutable('2024-12-31')));
    }

    public function testCanCheckIfCurrentlyValid(): void
    {
        $yesterday = new \DateTimeImmutable('yesterday');
        $tomorrow = new \DateTimeImmutable('tomorrow');
        $validity = new Validity($yesterday, $tomorrow);

        self::assertTrue($validity->isCurrentlyValid());
    }

    public function testIsNotCurrentlyValidIfBeforeFromDate(): void
    {
        $tomorrow = new \DateTimeImmutable('tomorrow');
        $nextWeek = new \DateTimeImmutable('+1 week');
        $validity = new Validity($tomorrow, $nextWeek);

        self::assertFalse($validity->isCurrentlyValid());
    }

    public function testIsNotCurrentlyValidIfAfterToDate(): void
    {
        $lastWeek = new \DateTimeImmutable('-1 week');
        $yesterday = new \DateTimeImmutable('yesterday');
        $validity = new Validity($lastWeek, $yesterday);

        self::assertFalse($validity->isCurrentlyValid());
    }

    public function testTwoValiditiesWithSameDatesAreEqual(): void
    {
        $from = new \DateTimeImmutable('2025-01-01');
        $to = new \DateTimeImmutable('2025-12-31');
        $validity1 = new Validity($from, $to);
        $validity2 = new Validity($from, $to);

        self::assertEquals($validity1, $validity2);
    }
}
