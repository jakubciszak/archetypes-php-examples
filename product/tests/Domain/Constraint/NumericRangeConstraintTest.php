<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Domain\Constraint;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Product\Constraint\NumericRangeConstraint;
use SoftwareArchetypes\Product\FeatureValueType;

final class NumericRangeConstraintTest extends TestCase
{
    public function testAcceptsValueWithinRange(): void
    {
        $constraint = new NumericRangeConstraint(10, 100);

        self::assertTrue($constraint->isSatisfiedBy(10));
        self::assertTrue($constraint->isSatisfiedBy(50));
        self::assertTrue($constraint->isSatisfiedBy(100));
    }

    public function testRejectsValueBelowMinimum(): void
    {
        $constraint = new NumericRangeConstraint(10, 100);

        self::assertFalse($constraint->isSatisfiedBy(9));
        self::assertFalse($constraint->isSatisfiedBy(-5));
    }

    public function testRejectsValueAboveMaximum(): void
    {
        $constraint = new NumericRangeConstraint(10, 100);

        self::assertFalse($constraint->isSatisfiedBy(101));
        self::assertFalse($constraint->isSatisfiedBy(999));
    }

    public function testRejectsNonIntegerValue(): void
    {
        $constraint = new NumericRangeConstraint(0, 100);

        self::assertFalse($constraint->isSatisfiedBy('50'));
        self::assertFalse($constraint->isSatisfiedBy(50.5));
        self::assertFalse($constraint->isSatisfiedBy(true));
    }

    public function testSupportsNegativeRange(): void
    {
        $constraint = new NumericRangeConstraint(-100, -10);

        self::assertTrue($constraint->isSatisfiedBy(-100));
        self::assertTrue($constraint->isSatisfiedBy(-50));
        self::assertTrue($constraint->isSatisfiedBy(-10));
        self::assertFalse($constraint->isSatisfiedBy(0));
        self::assertFalse($constraint->isSatisfiedBy(-101));
    }

    public function testSupportsRangeAcrossZero(): void
    {
        $constraint = new NumericRangeConstraint(-50, 50);

        self::assertTrue($constraint->isSatisfiedBy(-50));
        self::assertTrue($constraint->isSatisfiedBy(0));
        self::assertTrue($constraint->isSatisfiedBy(50));
    }

    public function testProvidesValueType(): void
    {
        $constraint = new NumericRangeConstraint(0, 100);

        self::assertEquals(FeatureValueType::INTEGER, $constraint->valueType());
    }

    public function testProvidesMinAndMaxValues(): void
    {
        $constraint = new NumericRangeConstraint(10, 100);

        self::assertEquals(10, $constraint->minimum());
        self::assertEquals(100, $constraint->maximum());
    }

    public function testRejectsInvalidRangeWhereMinIsGreaterThanMax(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Minimum value cannot be greater than maximum value');

        new NumericRangeConstraint(100, 10);
    }

    public function testAcceptsSingleValueRange(): void
    {
        $constraint = new NumericRangeConstraint(42, 42);

        self::assertTrue($constraint->isSatisfiedBy(42));
        self::assertFalse($constraint->isSatisfiedBy(41));
        self::assertFalse($constraint->isSatisfiedBy(43));
    }
}
