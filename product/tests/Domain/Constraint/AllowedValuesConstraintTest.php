<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Domain\Constraint;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Product\Constraint\AllowedValuesConstraint;
use SoftwareArchetypes\Product\FeatureValueType;

final class AllowedValuesConstraintTest extends TestCase
{
    public function testAcceptsValueInAllowedList(): void
    {
        $constraint = new AllowedValuesConstraint(
            FeatureValueType::TEXT,
            ['red', 'green', 'blue']
        );

        self::assertTrue($constraint->isSatisfiedBy('red'));
        self::assertTrue($constraint->isSatisfiedBy('green'));
        self::assertTrue($constraint->isSatisfiedBy('blue'));
    }

    public function testRejectsValueNotInAllowedList(): void
    {
        $constraint = new AllowedValuesConstraint(
            FeatureValueType::TEXT,
            ['small', 'medium', 'large']
        );

        self::assertFalse($constraint->isSatisfiedBy('extra-large'));
        self::assertFalse($constraint->isSatisfiedBy('tiny'));
    }

    public function testRejectsValueOfWrongType(): void
    {
        $constraint = new AllowedValuesConstraint(
            FeatureValueType::TEXT,
            ['one', 'two', 'three']
        );

        self::assertFalse($constraint->isSatisfiedBy(1));
        self::assertFalse($constraint->isSatisfiedBy(true));
    }

    public function testWorksWithIntegerValues(): void
    {
        $constraint = new AllowedValuesConstraint(
            FeatureValueType::INTEGER,
            [1, 2, 3, 5, 8, 13]
        );

        self::assertTrue($constraint->isSatisfiedBy(1));
        self::assertTrue($constraint->isSatisfiedBy(8));
        self::assertFalse($constraint->isSatisfiedBy(4));
        self::assertFalse($constraint->isSatisfiedBy(10));
    }

    public function testWorksWithBooleanValues(): void
    {
        $constraint = new AllowedValuesConstraint(
            FeatureValueType::BOOLEAN,
            [true]
        );

        self::assertTrue($constraint->isSatisfiedBy(true));
        self::assertFalse($constraint->isSatisfiedBy(false));
    }

    public function testProvidesValueType(): void
    {
        $constraint = new AllowedValuesConstraint(
            FeatureValueType::TEXT,
            ['value1', 'value2']
        );

        self::assertEquals(FeatureValueType::TEXT, $constraint->valueType());
    }

    public function testReturnsAllowedValues(): void
    {
        $allowedValues = ['red', 'green', 'blue'];
        $constraint = new AllowedValuesConstraint(
            FeatureValueType::TEXT,
            $allowedValues
        );

        self::assertEquals($allowedValues, $constraint->allowedValues());
    }

    public function testRejectsEmptyAllowedValuesList(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Allowed values list cannot be empty');

        new AllowedValuesConstraint(FeatureValueType::TEXT, []);
    }
}
