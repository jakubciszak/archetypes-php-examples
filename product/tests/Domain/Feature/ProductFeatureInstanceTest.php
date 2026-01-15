<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Domain\Feature;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Product\Constraint\AllowedValuesConstraint;
use SoftwareArchetypes\Product\Constraint\NumericRangeConstraint;
use SoftwareArchetypes\Product\Constraint\UnconstrainedConstraint;
use SoftwareArchetypes\Product\Feature\ProductFeatureInstance;
use SoftwareArchetypes\Product\Feature\ProductFeatureType;
use SoftwareArchetypes\Product\FeatureValueType;

final class ProductFeatureInstanceTest extends TestCase
{
    public function testCanBeCreatedWithFeatureTypeAndValue(): void
    {
        $featureType = new ProductFeatureType(
            'Color',
            new UnconstrainedConstraint(FeatureValueType::TEXT)
        );
        $featureInstance = new ProductFeatureInstance($featureType, 'red');

        self::assertSame($featureType, $featureInstance->type());
        self::assertEquals('red', $featureInstance->value());
    }

    public function testAcceptsValidValueAccordingToConstraint(): void
    {
        $featureType = new ProductFeatureType(
            'Size',
            new AllowedValuesConstraint(FeatureValueType::TEXT, ['small', 'medium', 'large'])
        );

        $instance = new ProductFeatureInstance($featureType, 'medium');
        self::assertEquals('medium', $instance->value());
    }

    public function testRejectsInvalidValueAccordingToConstraint(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value does not satisfy constraint for feature type');

        $featureType = new ProductFeatureType(
            'Size',
            new AllowedValuesConstraint(FeatureValueType::TEXT, ['small', 'medium', 'large'])
        );

        new ProductFeatureInstance($featureType, 'extra-large');
    }

    public function testRejectsValueOfWrongType(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Value does not satisfy constraint for feature type');

        $featureType = new ProductFeatureType(
            'Quantity',
            new NumericRangeConstraint(1, 100)
        );

        new ProductFeatureInstance($featureType, 'not-a-number');
    }

    public function testWorksWithIntegerValues(): void
    {
        $featureType = new ProductFeatureType(
            'Width',
            new NumericRangeConstraint(10, 100)
        );
        $instance = new ProductFeatureInstance($featureType, 50);

        self::assertEquals(50, $instance->value());
    }

    public function testWorksWithBooleanValues(): void
    {
        $featureType = new ProductFeatureType(
            'IsWaterproof',
            new UnconstrainedConstraint(FeatureValueType::BOOLEAN)
        );
        $instance = new ProductFeatureInstance($featureType, true);

        self::assertTrue($instance->value());
    }

    public function testTwoInstancesWithSameTypeAndValueAreEqual(): void
    {
        $featureType = new ProductFeatureType(
            'Color',
            new UnconstrainedConstraint(FeatureValueType::TEXT)
        );
        $instance1 = new ProductFeatureInstance($featureType, 'blue');
        $instance2 = new ProductFeatureInstance($featureType, 'blue');

        self::assertEquals($instance1, $instance2);
    }

    public function testProvidesFeatureName(): void
    {
        $featureType = new ProductFeatureType(
            'Material',
            new UnconstrainedConstraint(FeatureValueType::TEXT)
        );
        $instance = new ProductFeatureInstance($featureType, 'cotton');

        self::assertEquals('Material', $instance->name());
    }
}
