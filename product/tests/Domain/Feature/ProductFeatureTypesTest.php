<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Domain\Feature;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Product\Constraint\UnconstrainedConstraint;
use SoftwareArchetypes\Product\Feature\ProductFeatureType;
use SoftwareArchetypes\Product\Feature\ProductFeatureTypes;
use SoftwareArchetypes\Product\FeatureValueType;

final class ProductFeatureTypesTest extends TestCase
{
    public function testCanBeCreatedEmpty(): void
    {
        $types = new ProductFeatureTypes([]);

        self::assertTrue($types->isEmpty());
        self::assertEquals(0, $types->count());
    }

    public function testCanBeCreatedWithFeatureTypes(): void
    {
        $colorType = new ProductFeatureType(
            'Color',
            new UnconstrainedConstraint(FeatureValueType::TEXT)
        );
        $sizeType = new ProductFeatureType(
            'Size',
            new UnconstrainedConstraint(FeatureValueType::TEXT)
        );

        $types = new ProductFeatureTypes([$colorType, $sizeType]);

        self::assertFalse($types->isEmpty());
        self::assertEquals(2, $types->count());
    }

    public function testCanRetrieveFeatureTypeByName(): void
    {
        $colorType = new ProductFeatureType(
            'Color',
            new UnconstrainedConstraint(FeatureValueType::TEXT)
        );
        $sizeType = new ProductFeatureType(
            'Size',
            new UnconstrainedConstraint(FeatureValueType::TEXT)
        );

        $types = new ProductFeatureTypes([$colorType, $sizeType]);

        self::assertSame($colorType, $types->findByName('Color'));
        self::assertSame($sizeType, $types->findByName('Size'));
        self::assertNull($types->findByName('Weight'));
    }

    public function testCanCheckIfContainsFeatureType(): void
    {
        $colorType = new ProductFeatureType(
            'Color',
            new UnconstrainedConstraint(FeatureValueType::TEXT)
        );

        $types = new ProductFeatureTypes([$colorType]);

        self::assertTrue($types->hasFeatureType('Color'));
        self::assertFalse($types->hasFeatureType('Size'));
    }

    public function testCanIterateOverFeatureTypes(): void
    {
        $colorType = new ProductFeatureType(
            'Color',
            new UnconstrainedConstraint(FeatureValueType::TEXT)
        );
        $sizeType = new ProductFeatureType(
            'Size',
            new UnconstrainedConstraint(FeatureValueType::TEXT)
        );

        $types = new ProductFeatureTypes([$colorType, $sizeType]);

        $names = [];
        foreach ($types as $type) {
            $names[] = $type->name();
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
        $sizeType = new ProductFeatureType(
            'Size',
            new UnconstrainedConstraint(FeatureValueType::TEXT)
        );

        $types = new ProductFeatureTypes([$colorType, $sizeType]);
        $array = $types->toArray();

        self::assertCount(2, $array);
        self::assertContains($colorType, $array);
        self::assertContains($sizeType, $array);
    }

    public function testRejectsDuplicateFeatureTypeNames(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Duplicate feature type name');

        $colorType1 = new ProductFeatureType(
            'Color',
            new UnconstrainedConstraint(FeatureValueType::TEXT)
        );
        $colorType2 = new ProductFeatureType(
            'Color',
            new UnconstrainedConstraint(FeatureValueType::TEXT)
        );

        new ProductFeatureTypes([$colorType1, $colorType2]);
    }

    public function testRejectsInvalidArrayElement(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('All elements must be instances of ProductFeatureType');

        new ProductFeatureTypes(['not-a-feature-type']);
    }

    public function testFindByNameIsCaseInsensitive(): void
    {
        $colorType = new ProductFeatureType(
            'Color',
            new UnconstrainedConstraint(FeatureValueType::TEXT)
        );

        $types = new ProductFeatureTypes([$colorType]);

        self::assertSame($colorType, $types->findByName('color'));
        self::assertSame($colorType, $types->findByName('COLOR'));
        self::assertSame($colorType, $types->findByName('CoLoR'));
    }
}
