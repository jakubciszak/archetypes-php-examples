<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Domain;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Product\Constraint\UnconstrainedConstraint;
use SoftwareArchetypes\Product\Feature\ProductFeatureType;
use SoftwareArchetypes\Product\Feature\ProductFeatureTypes;
use SoftwareArchetypes\Product\FeatureValueType;
use SoftwareArchetypes\Product\Identifier\UuidProductIdentifier;
use SoftwareArchetypes\Product\ProductDescription;
use SoftwareArchetypes\Product\ProductName;
use SoftwareArchetypes\Product\ProductTrackingStrategy;
use SoftwareArchetypes\Product\ProductType;
use SoftwareArchetypes\Product\Unit;

final class ProductTypeTest extends TestCase
{
    public function testCanCreateUniqueProduct(): void
    {
        $productType = ProductType::unique(
            UuidProductIdentifier::random(),
            ProductName::of('Original Artwork'),
            ProductDescription::of('Unique painting by famous artist'),
            Unit::piece()
        );

        self::assertEquals(ProductTrackingStrategy::UNIQUE, $productType->trackingStrategy());
        self::assertTrue($productType->trackingStrategy()->isTrackedIndividually());
    }

    public function testCanCreateIndividuallyTrackedProduct(): void
    {
        $productType = ProductType::individuallyTracked(
            UuidProductIdentifier::random(),
            ProductName::of('Laptop'),
            ProductDescription::of('High-performance laptop'),
            Unit::piece()
        );

        self::assertEquals(ProductTrackingStrategy::INDIVIDUALLY_TRACKED, $productType->trackingStrategy());
        self::assertTrue($productType->trackingStrategy()->isTrackedIndividually());
        self::assertFalse($productType->trackingStrategy()->isTrackedByBatch());
    }

    public function testCanCreateBatchTrackedProduct(): void
    {
        $productType = ProductType::batchTracked(
            UuidProductIdentifier::random(),
            ProductName::of('Pharmaceutical Drug'),
            ProductDescription::of('Pain reliever medication'),
            Unit::piece()
        );

        self::assertEquals(ProductTrackingStrategy::BATCH_TRACKED, $productType->trackingStrategy());
        self::assertTrue($productType->trackingStrategy()->isTrackedByBatch());
        self::assertFalse($productType->trackingStrategy()->isTrackedIndividually());
    }

    public function testCanCreateIndividuallyAndBatchTrackedProduct(): void
    {
        $productType = ProductType::individuallyAndBatchTracked(
            UuidProductIdentifier::random(),
            ProductName::of('Medical Device'),
            ProductDescription::of('Surgical instrument with full traceability'),
            Unit::piece()
        );

        self::assertEquals(
            ProductTrackingStrategy::INDIVIDUALLY_AND_BATCH_TRACKED,
            $productType->trackingStrategy()
        );
        self::assertTrue($productType->trackingStrategy()->isTrackedIndividually());
        self::assertTrue($productType->trackingStrategy()->isTrackedByBatch());
    }

    public function testCanCreateIdenticalProduct(): void
    {
        $productType = ProductType::identical(
            UuidProductIdentifier::random(),
            ProductName::of('Bulk Sugar'),
            ProductDescription::of('White granulated sugar'),
            Unit::kilogram()
        );

        self::assertEquals(ProductTrackingStrategy::IDENTICAL, $productType->trackingStrategy());
        self::assertTrue($productType->trackingStrategy()->isInterchangeable());
        self::assertFalse($productType->trackingStrategy()->isTrackedIndividually());
        self::assertFalse($productType->trackingStrategy()->isTrackedByBatch());
    }

    public function testCanAccessProductTypeProperties(): void
    {
        $id = UuidProductIdentifier::random();
        $name = ProductName::of('Test Product');
        $description = ProductDescription::of('Test Description');
        $unit = Unit::piece();

        $productType = ProductType::unique($id, $name, $description, $unit);

        self::assertSame($id, $productType->id());
        self::assertSame($name, $productType->name());
        self::assertSame($description, $productType->description());
        self::assertSame($unit, $productType->preferredUnit());
    }

    public function testCanCreateProductTypeWithFeatures(): void
    {
        $colorFeature = new ProductFeatureType('Color', new UnconstrainedConstraint(FeatureValueType::TEXT));
        $sizeFeature = new ProductFeatureType('Size', new UnconstrainedConstraint(FeatureValueType::TEXT));
        $features = new ProductFeatureTypes([$colorFeature, $sizeFeature]);

        $productType = ProductType::identical(
            UuidProductIdentifier::random(),
            ProductName::of('T-Shirt'),
            ProductDescription::of('Cotton t-shirt'),
            Unit::piece(),
            $features
        );

        self::assertEquals(2, $productType->featureTypes()->count());
        self::assertTrue($productType->featureTypes()->hasFeatureType('Color'));
        self::assertTrue($productType->featureTypes()->hasFeatureType('Size'));
    }

    public function testCanBuildProductTypeUsingBuilder(): void
    {
        $id = UuidProductIdentifier::random();
        $name = ProductName::of('Custom Product');
        $description = ProductDescription::of('Built with builder pattern');
        $unit = Unit::meter();
        $colorFeature = new ProductFeatureType('Color', new UnconstrainedConstraint(FeatureValueType::TEXT));

        $productType = ProductType::builder()
            ->withId($id)
            ->withName($name)
            ->withDescription($description)
            ->withPreferredUnit($unit)
            ->withTrackingStrategy(ProductTrackingStrategy::INDIVIDUALLY_TRACKED)
            ->withFeatureType($colorFeature)
            ->build();

        self::assertSame($id, $productType->id());
        self::assertSame($name, $productType->name());
        self::assertSame($description, $productType->description());
        self::assertSame($unit, $productType->preferredUnit());
        self::assertEquals(ProductTrackingStrategy::INDIVIDUALLY_TRACKED, $productType->trackingStrategy());
        self::assertEquals(1, $productType->featureTypes()->count());
    }

    public function testBuilderWithMultipleFeatures(): void
    {
        $productType = ProductType::builder()
            ->withId(UuidProductIdentifier::random())
            ->withName(ProductName::of('Test'))
            ->withDescription(ProductDescription::of('Test'))
            ->withPreferredUnit(Unit::piece())
            ->withTrackingStrategy(ProductTrackingStrategy::IDENTICAL)
            ->withFeatureType(new ProductFeatureType('Color', new UnconstrainedConstraint(FeatureValueType::TEXT)))
            ->withFeatureType(new ProductFeatureType('Size', new UnconstrainedConstraint(FeatureValueType::TEXT)))
            ->withFeatureType(new ProductFeatureType('Weight', new UnconstrainedConstraint(FeatureValueType::INTEGER)))
            ->build();

        self::assertEquals(3, $productType->featureTypes()->count());
        self::assertTrue($productType->featureTypes()->hasFeatureType('Color'));
        self::assertTrue($productType->featureTypes()->hasFeatureType('Size'));
        self::assertTrue($productType->featureTypes()->hasFeatureType('Weight'));
    }

    public function testBuilderDefaultsToEmptyFeatures(): void
    {
        $productType = ProductType::builder()
            ->withId(UuidProductIdentifier::random())
            ->withName(ProductName::of('Simple Product'))
            ->withDescription(ProductDescription::of('No features'))
            ->withPreferredUnit(Unit::piece())
            ->withTrackingStrategy(ProductTrackingStrategy::IDENTICAL)
            ->build();

        self::assertTrue($productType->featureTypes()->isEmpty());
    }
}
