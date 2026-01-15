<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Domain\Constraint;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Product\Constraint\UnconstrainedConstraint;
use SoftwareArchetypes\Product\FeatureValueType;

final class UnconstrainedConstraintTest extends TestCase
{
    public function testAcceptsAnyTextValue(): void
    {
        $constraint = new UnconstrainedConstraint(FeatureValueType::TEXT);

        self::assertTrue($constraint->isSatisfiedBy('any text'));
        self::assertTrue($constraint->isSatisfiedBy(''));
        self::assertTrue($constraint->isSatisfiedBy('special chars !@#$%'));
    }

    public function testAcceptsAnyIntegerValue(): void
    {
        $constraint = new UnconstrainedConstraint(FeatureValueType::INTEGER);

        self::assertTrue($constraint->isSatisfiedBy(0));
        self::assertTrue($constraint->isSatisfiedBy(-100));
        self::assertTrue($constraint->isSatisfiedBy(999999));
    }

    public function testAcceptsAnyDecimalValue(): void
    {
        $constraint = new UnconstrainedConstraint(FeatureValueType::DECIMAL);

        self::assertTrue($constraint->isSatisfiedBy(\Brick\Math\BigDecimal::of('0.0')));
        self::assertTrue($constraint->isSatisfiedBy(\Brick\Math\BigDecimal::of('-123.456')));
        self::assertTrue($constraint->isSatisfiedBy(\Brick\Math\BigDecimal::of('999999.999')));
    }

    public function testAcceptsAnyDateValue(): void
    {
        $constraint = new UnconstrainedConstraint(FeatureValueType::DATE);

        self::assertTrue($constraint->isSatisfiedBy(new \DateTimeImmutable('2020-01-01')));
        self::assertTrue($constraint->isSatisfiedBy(new \DateTimeImmutable('2030-12-31')));
        self::assertTrue($constraint->isSatisfiedBy(new \DateTimeImmutable()));
    }

    public function testAcceptsAnyBooleanValue(): void
    {
        $constraint = new UnconstrainedConstraint(FeatureValueType::BOOLEAN);

        self::assertTrue($constraint->isSatisfiedBy(true));
        self::assertTrue($constraint->isSatisfiedBy(false));
    }

    public function testRejectsValueOfWrongType(): void
    {
        $constraint = new UnconstrainedConstraint(FeatureValueType::TEXT);

        self::assertFalse($constraint->isSatisfiedBy(123));
        self::assertFalse($constraint->isSatisfiedBy(true));
    }

    public function testProvidesValueType(): void
    {
        $constraint = new UnconstrainedConstraint(FeatureValueType::INTEGER);

        self::assertEquals(FeatureValueType::INTEGER, $constraint->valueType());
    }
}
