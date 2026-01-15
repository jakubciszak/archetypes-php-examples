<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Domain;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Product\Batch\BatchId;
use SoftwareArchetypes\Product\Constraint\UnconstrainedConstraint;
use SoftwareArchetypes\Product\Feature\ProductFeatureInstance;
use SoftwareArchetypes\Product\Feature\ProductFeatureInstances;
use SoftwareArchetypes\Product\Feature\ProductFeatureType;
use SoftwareArchetypes\Product\Feature\ProductFeatureTypes;
use SoftwareArchetypes\Product\FeatureValueType;
use SoftwareArchetypes\Product\Identifier\UuidProductIdentifier;
use SoftwareArchetypes\Product\ProductDescription;
use SoftwareArchetypes\Product\ProductInstance;
use SoftwareArchetypes\Product\ProductInstanceId;
use SoftwareArchetypes\Product\ProductName;
use SoftwareArchetypes\Product\ProductTrackingStrategy;
use SoftwareArchetypes\Product\ProductType;
use SoftwareArchetypes\Product\SerialNumber\TextualSerialNumber;
use SoftwareArchetypes\Product\Unit;

final class ProductInstanceTest extends TestCase
{
    public function testCanCreateIdenticalProductInstance(): void
    {
        $productType = ProductType::identical(
            UuidProductIdentifier::random(),
            ProductName::of('Bulk Sugar'),
            ProductDescription::of('White sugar'),
            Unit::kilogram()
        );

        $instance = ProductInstance::builder()
            ->withId(ProductInstanceId::random())
            ->withProductType($productType)
            ->withQuantity(10)
            ->build();

        self::assertSame($productType, $instance->productType());
        self::assertEquals(10, $instance->quantity());
        self::assertNull($instance->serialNumber());
        self::assertNull($instance->batchId());
    }

    public function testCanCreateIndividuallyTrackedProductWithSerialNumber(): void
    {
        $productType = ProductType::individuallyTracked(
            UuidProductIdentifier::random(),
            ProductName::of('Laptop'),
            ProductDescription::of('High-performance laptop'),
            Unit::piece()
        );

        $serialNumber = TextualSerialNumber::of('SN123456789');

        $instance = ProductInstance::builder()
            ->withId(ProductInstanceId::random())
            ->withProductType($productType)
            ->withSerialNumber($serialNumber)
            ->withQuantity(1)
            ->build();

        self::assertSame($serialNumber, $instance->serialNumber());
        self::assertNull($instance->batchId());
    }

    public function testCanCreateBatchTrackedProductWithBatchId(): void
    {
        $productType = ProductType::batchTracked(
            UuidProductIdentifier::random(),
            ProductName::of('Pharmaceutical Drug'),
            ProductDescription::of('Pain reliever'),
            Unit::piece()
        );

        $batchId = BatchId::random();

        $instance = ProductInstance::builder()
            ->withId(ProductInstanceId::random())
            ->withProductType($productType)
            ->withBatchId($batchId)
            ->withQuantity(100)
            ->build();

        self::assertSame($batchId, $instance->batchId());
        self::assertNull($instance->serialNumber());
    }

    public function testCanCreateIndividuallyAndBatchTrackedProduct(): void
    {
        $productType = ProductType::individuallyAndBatchTracked(
            UuidProductIdentifier::random(),
            ProductName::of('Medical Device'),
            ProductDescription::of('Surgical instrument'),
            Unit::piece()
        );

        $serialNumber = TextualSerialNumber::of('MD-2024-001');
        $batchId = BatchId::random();

        $instance = ProductInstance::builder()
            ->withId(ProductInstanceId::random())
            ->withProductType($productType)
            ->withSerialNumber($serialNumber)
            ->withBatchId($batchId)
            ->withQuantity(1)
            ->build();

        self::assertSame($serialNumber, $instance->serialNumber());
        self::assertSame($batchId, $instance->batchId());
    }

    public function testThrowsExceptionWhenSerialNumberRequiredButNotProvided(): void
    {
        $productType = ProductType::individuallyTracked(
            UuidProductIdentifier::random(),
            ProductName::of('Laptop'),
            ProductDescription::of('Laptop'),
            Unit::piece()
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Serial number is required for individually tracked products');

        ProductInstance::builder()
            ->withId(ProductInstanceId::random())
            ->withProductType($productType)
            ->withQuantity(1)
            ->build();
    }

    public function testThrowsExceptionWhenBatchIdRequiredButNotProvided(): void
    {
        $productType = ProductType::batchTracked(
            UuidProductIdentifier::random(),
            ProductName::of('Drug'),
            ProductDescription::of('Medication'),
            Unit::piece()
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Batch ID is required for batch tracked products');

        ProductInstance::builder()
            ->withId(ProductInstanceId::random())
            ->withProductType($productType)
            ->withQuantity(100)
            ->build();
    }

    public function testThrowsExceptionWhenBothSerialNumberAndBatchIdRequiredButOnlyOneProvided(): void
    {
        $productType = ProductType::individuallyAndBatchTracked(
            UuidProductIdentifier::random(),
            ProductName::of('Medical Device'),
            ProductDescription::of('Device'),
            Unit::piece()
        );

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Both serial number and batch ID are required');

        ProductInstance::builder()
            ->withId(ProductInstanceId::random())
            ->withProductType($productType)
            ->withSerialNumber(TextualSerialNumber::of('SN123'))
            ->withQuantity(1)
            ->build();
    }

    public function testCanCreateProductInstanceWithFeatures(): void
    {
        $colorFeature = new ProductFeatureType('Color', new UnconstrainedConstraint(FeatureValueType::TEXT));
        $features = new ProductFeatureTypes([$colorFeature]);

        $productType = ProductType::identical(
            UuidProductIdentifier::random(),
            ProductName::of('T-Shirt'),
            ProductDescription::of('Cotton t-shirt'),
            Unit::piece(),
            $features
        );

        $colorInstance = new ProductFeatureInstance($colorFeature, 'Red');
        $featureInstances = new ProductFeatureInstances([$colorInstance]);

        $instance = ProductInstance::builder()
            ->withId(ProductInstanceId::random())
            ->withProductType($productType)
            ->withQuantity(5)
            ->withFeatures($featureInstances)
            ->build();

        self::assertEquals(1, $instance->features()->count());
    }

    public function testBuilderValidatesRequiredFields(): void
    {
        $this->expectException(InvalidArgumentException::class);

        ProductInstance::builder()->build();
    }

    public function testCanAccessAllProperties(): void
    {
        $id = ProductInstanceId::random();
        $productType = ProductType::identical(
            UuidProductIdentifier::random(),
            ProductName::of('Test'),
            ProductDescription::of('Test'),
            Unit::piece()
        );
        $quantity = 42;

        $instance = ProductInstance::builder()
            ->withId($id)
            ->withProductType($productType)
            ->withQuantity($quantity)
            ->build();

        self::assertSame($id, $instance->id());
        self::assertSame($productType, $instance->productType());
        self::assertEquals($quantity, $instance->quantity());
        self::assertTrue($instance->features()->isEmpty());
    }
}
