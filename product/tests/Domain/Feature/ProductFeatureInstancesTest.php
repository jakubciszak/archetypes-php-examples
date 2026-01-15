<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Domain\Feature;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Product\Constraint\UnconstrainedConstraint;
use SoftwareArchetypes\Product\Feature\ProductFeatureInstance;
use SoftwareArchetypes\Product\Feature\ProductFeatureInstances;
use SoftwareArchetypes\Product\Feature\ProductFeatureType;
use SoftwareArchetypes\Product\FeatureValueType;

final class ProductFeatureInstancesTest extends TestCase
{
    public function testCanBeCreatedEmpty(): void
    {
        $instances = new ProductFeatureInstances([]);

        self::assertTrue($instances->isEmpty());
        self::assertEquals(0, $instances->count());
    }

    public function testCanBeCreatedWithFeatureInstances(): void
    {
        $colorType = new ProductFeatureType(
            'Color',
            new UnconstrainedConstraint(FeatureValueType::TEXT)
        );
        $sizeType = new ProductFeatureType(
            'Size',
            new UnconstrainedConstraint(FeatureValueType::TEXT)
        );

        $colorInstance = new ProductFeatureInstance($colorType, 'red');
        $sizeInstance = new ProductFeatureInstance($sizeType, 'large');

        $instances = new ProductFeatureInstances([$colorInstance, $sizeInstance]);

        self::assertFalse($instances->isEmpty());
        self::assertEquals(2, $instances->count());
    }

    public function testCanRetrieveFeatureInstanceByName(): void
    {
        $colorType = new ProductFeatureType(
            'Color',
            new UnconstrainedConstraint(FeatureValueType::TEXT)
        );
        $sizeType = new ProductFeatureType(
            'Size',
            new UnconstrainedConstraint(FeatureValueType::TEXT)
        );

        $colorInstance = new ProductFeatureInstance($colorType, 'red');
        $sizeInstance = new ProductFeatureInstance($sizeType, 'large');

        $instances = new ProductFeatureInstances([$colorInstance, $sizeInstance]);

        self::assertSame($colorInstance, $instances->findByName('Color'));
        self::assertSame($sizeInstance, $instances->findByName('Size'));
        self::assertNull($instances->findByName('Weight'));
    }

    public function testCanCheckIfContainsFeature(): void
    {
        $colorType = new ProductFeatureType(
            'Color',
            new UnconstrainedConstraint(FeatureValueType::TEXT)
        );
        $colorInstance = new ProductFeatureInstance($colorType, 'red');

        $instances = new ProductFeatureInstances([$colorInstance]);

        self::assertTrue($instances->hasFeature('Color'));
        self::assertFalse($instances->hasFeature('Size'));
    }

    public function testCanIterateOverFeatureInstances(): void
    {
        $colorType = new ProductFeatureType(
            'Color',
            new UnconstrainedConstraint(FeatureValueType::TEXT)
        );
        $sizeType = new ProductFeatureType(
            'Size',
            new UnconstrainedConstraint(FeatureValueType::TEXT)
        );

        $colorInstance = new ProductFeatureInstance($colorType, 'red');
        $sizeInstance = new ProductFeatureInstance($sizeType, 'large');

        $instances = new ProductFeatureInstances([$colorInstance, $sizeInstance]);

        $names = [];
        foreach ($instances as $instance) {
            $names[] = $instance->name();
        }

        self::assertContains('Color', $names);
        self::assertContains('Size', $names);
    }

    public function testCanConvertToArray(): void
    {
        $colorType = new ProductFeatureType(
            'Color',
            new UnconstrainedConstraint(FeatureValueType::TEXT)
        );
        $colorInstance = new ProductFeatureInstance($colorType, 'red');

        $instances = new ProductFeatureInstances([$colorInstance]);
        $array = $instances->toArray();

        self::assertCount(1, $array);
        self::assertContains($colorInstance, $array);
    }

    public function testRejectsDuplicateFeatureNames(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate feature name');

        $colorType = new ProductFeatureType(
            'Color',
            new UnconstrainedConstraint(FeatureValueType::TEXT)
        );

        $colorInstance1 = new ProductFeatureInstance($colorType, 'red');
        $colorInstance2 = new ProductFeatureInstance($colorType, 'blue');

        new ProductFeatureInstances([$colorInstance1, $colorInstance2]);
    }

    public function testRejectsInvalidArrayElement(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All elements must be instances of ProductFeatureInstance');

        new ProductFeatureInstances(['not-a-feature-instance']);
    }

    public function testFindByNameIsCaseInsensitive(): void
    {
        $colorType = new ProductFeatureType(
            'Color',
            new UnconstrainedConstraint(FeatureValueType::TEXT)
        );
        $colorInstance = new ProductFeatureInstance($colorType, 'red');

        $instances = new ProductFeatureInstances([$colorInstance]);

        self::assertSame($colorInstance, $instances->findByName('color'));
        self::assertSame($colorInstance, $instances->findByName('COLOR'));
        self::assertSame($colorInstance, $instances->findByName('CoLoR'));
    }
}
