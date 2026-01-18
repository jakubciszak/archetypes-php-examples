<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Tests\Loyalty\Domain;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Points;

final class PointsTest extends TestCase
{
    public function testCreatesPointsWithAmount(): void
    {
        $points = Points::of(100);

        self::assertSame(100, $points->amount());
    }

    public function testCreatesZeroPoints(): void
    {
        $points = Points::zero();

        self::assertTrue($points->isZero());
        self::assertSame(0, $points->amount());
    }

    public function testCannotCreateNegativePoints(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Points amount cannot be negative');

        Points::of(-10);
    }

    public function testAddsPoints(): void
    {
        $points1 = Points::of(100);
        $points2 = Points::of(50);

        $result = $points1->add($points2);

        self::assertSame(150, $result->amount());
    }

    public function testSubtractsPoints(): void
    {
        $points1 = Points::of(100);
        $points2 = Points::of(50);

        $result = $points1->subtract($points2);

        self::assertSame(50, $result->amount());
    }

    public function testCannotSubtractMoreThanAvailable(): void
    {
        $points1 = Points::of(50);
        $points2 = Points::of(100);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot subtract more points than available');

        $points1->subtract($points2);
    }

    public function testIsPositive(): void
    {
        $points = Points::of(100);

        self::assertTrue($points->isPositive());
    }

    public function testZeroIsNotPositive(): void
    {
        $points = Points::zero();

        self::assertFalse($points->isPositive());
    }

    public function testEquals(): void
    {
        $points1 = Points::of(100);
        $points2 = Points::of(100);
        $points3 = Points::of(50);

        self::assertTrue($points1->equals($points2));
        self::assertFalse($points1->equals($points3));
    }

    public function testComparesPoints(): void
    {
        $points1 = Points::of(100);
        $points2 = Points::of(50);
        $points3 = Points::of(150);

        self::assertSame(1, $points1->compareTo($points2));
        self::assertSame(-1, $points1->compareTo($points3));
        self::assertSame(0, $points1->compareTo(Points::of(100)));
    }

    public function testGreaterThan(): void
    {
        $points1 = Points::of(100);
        $points2 = Points::of(50);

        self::assertTrue($points1->greaterThan($points2));
        self::assertFalse($points2->greaterThan($points1));
    }

    public function testGreaterThanOrEqual(): void
    {
        $points1 = Points::of(100);
        $points2 = Points::of(100);
        $points3 = Points::of(50);

        self::assertTrue($points1->greaterThanOrEqual($points2));
        self::assertTrue($points1->greaterThanOrEqual($points3));
        self::assertFalse($points3->greaterThanOrEqual($points1));
    }
}
