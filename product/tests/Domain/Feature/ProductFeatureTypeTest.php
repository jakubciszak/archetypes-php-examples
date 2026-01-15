<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Domain\Feature;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Product\Constraint\AllowedValuesConstraint;
use SoftwareArchetypes\Product\Constraint\NumericRangeConstraint;
use SoftwareArchetypes\Product\Constraint\UnconstrainedConstraint;
use SoftwareArchetypes\Product\Feature\ProductFeatureType;
use SoftwareArchetypes\Product\FeatureValueType;

final class ProductFeatureTypeTest extends TestCase
{
    public function testCanBeCreatedWithNameAndConstraint(): void
    {
        $constraint = new UnconstrainedConstraint(FeatureValueType::TEXT);
        $featureType = new ProductFeatureType('Color', $constraint);

        self::assertEquals('Color', $featureType->name());
        self::assertSame($constraint, $featureType->constraint());
    }

    public function testTwoFeatureTypesWithSameNameAndConstraintAreEqual(): void
    {
        $constraint = new UnconstrainedConstraint(FeatureValueType::TEXT);
        $feature1 = new ProductFeatureType('Size', $constraint);
        $feature2 = new ProductFeatureType('Size', $constraint);

        self::assertEquals($feature1, $feature2);
    }

    public function testProvidesValueTypeFromConstraint(): void
    {
        $constraint = new NumericRangeConstraint(1, 100);
        $featureType = new ProductFeatureType('Quantity', $constraint);

        self::assertEquals(FeatureValueType::INTEGER, $featureType->valueType());
    }

    public function testValidatesValueUsingConstraint(): void
    {
        $constraint = new AllowedValuesConstraint(
            FeatureValueType::TEXT,
            ['small', 'medium', 'large']
        );
        $featureType = new ProductFeatureType('Size', $constraint);

        self::assertTrue($featureType->isValidValue('small'));
        self::assertTrue($featureType->isValidValue('medium'));
        self::assertTrue($featureType->isValidValue('large'));
        self::assertFalse($featureType->isValidValue('extra-large'));
        self::assertFalse($featureType->isValidValue(123));
    }

    public function testRejectsEmptyName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Feature type name cannot be empty');

        $constraint = new UnconstrainedConstraint(FeatureValueType::TEXT);
        new ProductFeatureType('', $constraint);
    }

    public function testRejectsWhitespaceOnlyName(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Feature type name cannot be empty');

        $constraint = new UnconstrainedConstraint(FeatureValueType::TEXT);
        new ProductFeatureType('   ', $constraint);
    }

    public function testTrimsWhitespaceFromName(): void
    {
        $constraint = new UnconstrainedConstraint(FeatureValueType::TEXT);
        $featureType = new ProductFeatureType('  Color  ', $constraint);

        self::assertEquals('Color', $featureType->name());
    }
}
