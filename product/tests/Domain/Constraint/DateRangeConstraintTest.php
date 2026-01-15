<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Domain\Constraint;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Product\Constraint\DateRangeConstraint;
use SoftwareArchetypes\Product\FeatureValueType;

final class DateRangeConstraintTest extends TestCase
{
    public function testAcceptsDateWithinRange(): void
    {
        $constraint = new DateRangeConstraint(
            new DateTimeImmutable('2025-01-01'),
            new DateTimeImmutable('2025-12-31')
        );

        self::assertTrue($constraint->isSatisfiedBy(new DateTimeImmutable('2025-01-01')));
        self::assertTrue($constraint->isSatisfiedBy(new DateTimeImmutable('2025-06-15')));
        self::assertTrue($constraint->isSatisfiedBy(new DateTimeImmutable('2025-12-31')));
    }

    public function testRejectsDateBeforeMinimum(): void
    {
        $constraint = new DateRangeConstraint(
            new DateTimeImmutable('2025-01-01'),
            new DateTimeImmutable('2025-12-31')
        );

        self::assertFalse($constraint->isSatisfiedBy(new DateTimeImmutable('2024-12-31')));
        self::assertFalse($constraint->isSatisfiedBy(new DateTimeImmutable('2024-01-01')));
    }

    public function testRejectsDateAfterMaximum(): void
    {
        $constraint = new DateRangeConstraint(
            new DateTimeImmutable('2025-01-01'),
            new DateTimeImmutable('2025-12-31')
        );

        self::assertFalse($constraint->isSatisfiedBy(new DateTimeImmutable('2026-01-01')));
        self::assertFalse($constraint->isSatisfiedBy(new DateTimeImmutable('2026-12-31')));
    }

    public function testRejectsNonDateTimeValue(): void
    {
        $constraint = new DateRangeConstraint(
            new DateTimeImmutable('2025-01-01'),
            new DateTimeImmutable('2025-12-31')
        );

        self::assertFalse($constraint->isSatisfiedBy('2025-06-15'));
        self::assertFalse($constraint->isSatisfiedBy(20250615));
    }

    public function testComparesOnlyDatePortionIgnoringTime(): void
    {
        $constraint = new DateRangeConstraint(
            new DateTimeImmutable('2025-01-01 00:00:00'),
            new DateTimeImmutable('2025-12-31 23:59:59')
        );

        self::assertTrue($constraint->isSatisfiedBy(new DateTimeImmutable('2025-06-15 10:30:45')));
        self::assertTrue($constraint->isSatisfiedBy(new DateTimeImmutable('2025-01-01 23:59:59')));
        self::assertTrue($constraint->isSatisfiedBy(new DateTimeImmutable('2025-12-31 00:00:00')));
    }

    public function testProvidesValueType(): void
    {
        $constraint = new DateRangeConstraint(
            new DateTimeImmutable('2025-01-01'),
            new DateTimeImmutable('2025-12-31')
        );

        self::assertEquals(FeatureValueType::DATE, $constraint->valueType());
    }

    public function testProvidesMinAndMaxDates(): void
    {
        $min = new DateTimeImmutable('2025-01-01');
        $max = new DateTimeImmutable('2025-12-31');
        $constraint = new DateRangeConstraint($min, $max);

        self::assertEquals($min->format('Y-m-d'), $constraint->minimum()->format('Y-m-d'));
        self::assertEquals($max->format('Y-m-d'), $constraint->maximum()->format('Y-m-d'));
    }

    public function testRejectsInvalidRangeWhereMinIsAfterMax(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Minimum date cannot be after maximum date');

        new DateRangeConstraint(
            new DateTimeImmutable('2025-12-31'),
            new DateTimeImmutable('2025-01-01')
        );
    }

    public function testAcceptsSingleDateRange(): void
    {
        $date = new DateTimeImmutable('2025-06-15');
        $constraint = new DateRangeConstraint($date, $date);

        self::assertTrue($constraint->isSatisfiedBy(new DateTimeImmutable('2025-06-15')));
        self::assertFalse($constraint->isSatisfiedBy(new DateTimeImmutable('2025-06-14')));
        self::assertFalse($constraint->isSatisfiedBy(new DateTimeImmutable('2025-06-16')));
    }

    public function testWorksWithHistoricalDates(): void
    {
        $constraint = new DateRangeConstraint(
            new DateTimeImmutable('1900-01-01'),
            new DateTimeImmutable('2000-12-31')
        );

        self::assertTrue($constraint->isSatisfiedBy(new DateTimeImmutable('1950-05-20')));
        self::assertTrue($constraint->isSatisfiedBy(new DateTimeImmutable('1900-01-01')));
        self::assertFalse($constraint->isSatisfiedBy(new DateTimeImmutable('2001-01-01')));
    }

    public function testWorksWithFutureDates(): void
    {
        $constraint = new DateRangeConstraint(
            new DateTimeImmutable('2030-01-01'),
            new DateTimeImmutable('2040-12-31')
        );

        self::assertTrue($constraint->isSatisfiedBy(new DateTimeImmutable('2035-06-15')));
        self::assertFalse($constraint->isSatisfiedBy(new DateTimeImmutable('2025-01-01')));
        self::assertFalse($constraint->isSatisfiedBy(new DateTimeImmutable('2041-01-01')));
    }
}
