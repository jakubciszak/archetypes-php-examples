<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Domain\Constraint;

use Brick\Math\BigDecimal;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Product\Constraint\DecimalRangeConstraint;
use SoftwareArchetypes\Product\FeatureValueType;

final class DecimalRangeConstraintTest extends TestCase
{
    public function testAcceptsValueWithinRange(): void
    {
        $constraint = new DecimalRangeConstraint(
            BigDecimal::of('10.5'),
            BigDecimal::of('100.99')
        );

        self::assertTrue($constraint->isSatisfiedBy(BigDecimal::of('10.5')));
        self::assertTrue($constraint->isSatisfiedBy(BigDecimal::of('50.75')));
        self::assertTrue($constraint->isSatisfiedBy(BigDecimal::of('100.99')));
    }

    public function testRejectsValueBelowMinimum(): void
    {
        $constraint = new DecimalRangeConstraint(
            BigDecimal::of('10.0'),
            BigDecimal::of('100.0')
        );

        self::assertFalse($constraint->isSatisfiedBy(BigDecimal::of('9.99')));
        self::assertFalse($constraint->isSatisfiedBy(BigDecimal::of('-5.0')));
    }

    public function testRejectsValueAboveMaximum(): void
    {
        $constraint = new DecimalRangeConstraint(
            BigDecimal::of('10.0'),
            BigDecimal::of('100.0')
        );

        self::assertFalse($constraint->isSatisfiedBy(BigDecimal::of('100.01')));
        self::assertFalse($constraint->isSatisfiedBy(BigDecimal::of('999.99')));
    }

    public function testRejectsNonBigDecimalValue(): void
    {
        $constraint = new DecimalRangeConstraint(
            BigDecimal::of('0.0'),
            BigDecimal::of('100.0')
        );

        self::assertFalse($constraint->isSatisfiedBy(50.5));
        self::assertFalse($constraint->isSatisfiedBy('50.5'));
        self::assertFalse($constraint->isSatisfiedBy(50));
    }

    public function testSupportsNegativeRange(): void
    {
        $constraint = new DecimalRangeConstraint(
            BigDecimal::of('-100.5'),
            BigDecimal::of('-10.25')
        );

        self::assertTrue($constraint->isSatisfiedBy(BigDecimal::of('-100.5')));
        self::assertTrue($constraint->isSatisfiedBy(BigDecimal::of('-50.0')));
        self::assertTrue($constraint->isSatisfiedBy(BigDecimal::of('-10.25')));
        self::assertFalse($constraint->isSatisfiedBy(BigDecimal::of('0.0')));
        self::assertFalse($constraint->isSatisfiedBy(BigDecimal::of('-101.0')));
    }

    public function testSupportsRangeAcrossZero(): void
    {
        $constraint = new DecimalRangeConstraint(
            BigDecimal::of('-50.5'),
            BigDecimal::of('50.5')
        );

        self::assertTrue($constraint->isSatisfiedBy(BigDecimal::of('-50.5')));
        self::assertTrue($constraint->isSatisfiedBy(BigDecimal::of('0.0')));
        self::assertTrue($constraint->isSatisfiedBy(BigDecimal::of('50.5')));
    }

    public function testProvidesValueType(): void
    {
        $constraint = new DecimalRangeConstraint(
            BigDecimal::of('0.0'),
            BigDecimal::of('100.0')
        );

        self::assertEquals(FeatureValueType::DECIMAL, $constraint->valueType());
    }

    public function testProvidesMinAndMaxValues(): void
    {
        $min = BigDecimal::of('10.5');
        $max = BigDecimal::of('100.75');
        $constraint = new DecimalRangeConstraint($min, $max);

        self::assertTrue($constraint->minimum()->isEqualTo($min));
        self::assertTrue($constraint->maximum()->isEqualTo($max));
    }

    public function testRejectsInvalidRangeWhereMinIsGreaterThanMax(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Minimum value cannot be greater than maximum value');

        new DecimalRangeConstraint(
            BigDecimal::of('100.0'),
            BigDecimal::of('10.0')
        );
    }

    public function testAcceptsSingleValueRange(): void
    {
        $constraint = new DecimalRangeConstraint(
            BigDecimal::of('42.42'),
            BigDecimal::of('42.42')
        );

        self::assertTrue($constraint->isSatisfiedBy(BigDecimal::of('42.42')));
        self::assertFalse($constraint->isSatisfiedBy(BigDecimal::of('42.41')));
        self::assertFalse($constraint->isSatisfiedBy(BigDecimal::of('42.43')));
    }

    public function testHandlesDifferentScales(): void
    {
        $constraint = new DecimalRangeConstraint(
            BigDecimal::of('10'),
            BigDecimal::of('100')
        );

        self::assertTrue($constraint->isSatisfiedBy(BigDecimal::of('50.0')));
        self::assertTrue($constraint->isSatisfiedBy(BigDecimal::of('50.00')));
        self::assertTrue($constraint->isSatisfiedBy(BigDecimal::of('10')));
        self::assertTrue($constraint->isSatisfiedBy(BigDecimal::of('100.0')));
    }
}
