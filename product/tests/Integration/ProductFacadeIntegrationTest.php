<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Integration;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Product\Application\ProductFacade;
use SoftwareArchetypes\Product\Batch\BatchId;
use SoftwareArchetypes\Product\Constraint\UnconstrainedConstraint;
use SoftwareArchetypes\Product\Feature\ProductFeatureInstance;
use SoftwareArchetypes\Product\Feature\ProductFeatureInstances;
use SoftwareArchetypes\Product\Feature\ProductFeatureType;
use SoftwareArchetypes\Product\Feature\ProductFeatureTypes;
use SoftwareArchetypes\Product\FeatureValueType;
use SoftwareArchetypes\Product\Identifier\UuidProductIdentifier;
use SoftwareArchetypes\Product\Infrastructure\InMemoryProductTypeRepository;
use SoftwareArchetypes\Product\ProductDescription;
use SoftwareArchetypes\Product\ProductInstance;
use SoftwareArchetypes\Product\ProductInstanceId;
use SoftwareArchetypes\Product\ProductName;
use SoftwareArchetypes\Product\ProductType;
use SoftwareArchetypes\Product\SerialNumber\TextualSerialNumber;
use SoftwareArchetypes\Product\Unit;

final class ProductFacadeIntegrationTest extends TestCase
{
    private ProductFacade $facade;
    private InMemoryProductTypeRepository $repository;

    protected function setUp(): void
    {
        $this->repository = new InMemoryProductTypeRepository();
        $this->facade = new ProductFacade($this->repository);
    }

    public function testCanDefineAndRetrieveIdenticalProduct(): void
    {
        $id = UuidProductIdentifier::random();
        $productType = ProductType::identical(
            $id,
            ProductName::of('Bulk Sugar'),
            ProductDescription::of('White granulated sugar'),
            Unit::kilogram()
        );

        $this->facade->defineProductType($productType);

        $retrieved = $this->facade->getProductType($id);

        self::assertNotNull($retrieved);
        self::assertSame($productType, $retrieved);
        self::assertEquals('Bulk Sugar', $retrieved->name()->value());
        self::assertTrue($this->facade->productTypeExists($id));
    }

    public function testCanDefineIndividuallyTrackedProduct(): void
    {
        $id = UuidProductIdentifier::random();
        $productType = ProductType::individuallyTracked(
            $id,
            ProductName::of('Laptop'),
            ProductDescription::of('High-performance laptop with serial number tracking'),
            Unit::piece()
        );

        $this->facade->defineProductType($productType);

        $retrieved = $this->facade->getProductType($id);

        self::assertNotNull($retrieved);
        self::assertTrue($retrieved->trackingStrategy()->isTrackedIndividually());
        self::assertFalse($retrieved->trackingStrategy()->isTrackedByBatch());
    }

    public function testCanDefineBatchTrackedProduct(): void
    {
        $id = UuidProductIdentifier::random();
        $productType = ProductType::batchTracked(
            $id,
            ProductName::of('Pharmaceutical Drug'),
            ProductDescription::of('Pain reliever medication with batch tracking'),
            Unit::piece()
        );

        $this->facade->defineProductType($productType);

        $retrieved = $this->facade->getProductType($id);

        self::assertNotNull($retrieved);
        self::assertFalse($retrieved->trackingStrategy()->isTrackedIndividually());
        self::assertTrue($retrieved->trackingStrategy()->isTrackedByBatch());
    }

    public function testCanDefineProductWithFeatures(): void
    {
        $colorFeature = new ProductFeatureType('Color', new UnconstrainedConstraint(FeatureValueType::TEXT));
        $sizeFeature = new ProductFeatureType('Size', new UnconstrainedConstraint(FeatureValueType::TEXT));
        $features = new ProductFeatureTypes([$colorFeature, $sizeFeature]);

        $id = UuidProductIdentifier::random();
        $productType = ProductType::identical(
            $id,
            ProductName::of('T-Shirt'),
            ProductDescription::of('Cotton t-shirt with configurable features'),
            Unit::piece(),
            $features
        );

        $this->facade->defineProductType($productType);

        $retrieved = $this->facade->getProductType($id);

        self::assertNotNull($retrieved);
        self::assertEquals(2, $retrieved->featureTypes()->count());
        self::assertTrue($retrieved->featureTypes()->hasFeatureType('Color'));
        self::assertTrue($retrieved->featureTypes()->hasFeatureType('Size'));
    }

    public function testCanRetrieveAllProductTypes(): void
    {
        $product1 = ProductType::identical(
            UuidProductIdentifier::random(),
            ProductName::of('Product 1'),
            ProductDescription::of('First product'),
            Unit::piece()
        );

        $product2 = ProductType::identical(
            UuidProductIdentifier::random(),
            ProductName::of('Product 2'),
            ProductDescription::of('Second product'),
            Unit::kilogram()
        );

        $this->facade->defineProductType($product1);
        $this->facade->defineProductType($product2);

        $allProducts = $this->facade->getAllProductTypes();

        self::assertCount(2, $allProducts);
    }

    public function testReturnsNullWhenProductTypeNotFound(): void
    {
        $result = $this->facade->getProductType(UuidProductIdentifier::random());

        self::assertNull($result);
    }

    public function testProductTypeExistsReturnsFalseWhenNotFound(): void
    {
        $exists = $this->facade->productTypeExists(UuidProductIdentifier::random());

        self::assertFalse($exists);
    }

    public function testEndToEndScenarioWithProductInstanceCreation(): void
    {
        // Define a product type
        $productTypeId = UuidProductIdentifier::random();
        $productType = ProductType::individuallyTracked(
            $productTypeId,
            ProductName::of('Smartphone'),
            ProductDescription::of('High-end smartphone'),
            Unit::piece()
        );

        $this->facade->defineProductType($productType);

        // Verify product type was saved
        $retrievedType = $this->facade->getProductType($productTypeId);
        self::assertNotNull($retrievedType);

        // Create a product instance based on the product type
        $serialNumber = TextualSerialNumber::of('IMEI-123456789');
        $instance = ProductInstance::builder()
            ->withId(ProductInstanceId::random())
            ->withProductType($retrievedType)
            ->withSerialNumber($serialNumber)
            ->withQuantity(1)
            ->build();

        // Verify the instance was created with correct properties
        self::assertSame($retrievedType, $instance->productType());
        self::assertSame($serialNumber, $instance->serialNumber());
        self::assertEquals(1, $instance->quantity());
    }

    public function testEndToEndScenarioWithBatchTrackedProduct(): void
    {
        // Define a batch-tracked product type
        $productTypeId = UuidProductIdentifier::random();
        $productType = ProductType::batchTracked(
            $productTypeId,
            ProductName::of('Vaccine'),
            ProductDescription::of('COVID-19 vaccine'),
            Unit::piece()
        );

        $this->facade->defineProductType($productType);

        // Create instances from the same batch
        $batchId = BatchId::random();
        $retrievedType = $this->facade->getProductType($productTypeId);
        self::assertNotNull($retrievedType);

        $instance1 = ProductInstance::builder()
            ->withId(ProductInstanceId::random())
            ->withProductType($retrievedType)
            ->withBatchId($batchId)
            ->withQuantity(50)
            ->build();

        $instance2 = ProductInstance::builder()
            ->withId(ProductInstanceId::random())
            ->withProductType($retrievedType)
            ->withBatchId($batchId)
            ->withQuantity(50)
            ->build();

        // Verify both instances share the same batch
        self::assertNotNull($instance1->batchId());
        self::assertNotNull($instance2->batchId());
        self::assertEquals($batchId->asString(), $instance1->batchId()->asString());
        self::assertEquals($batchId->asString(), $instance2->batchId()->asString());
    }

    public function testEndToEndScenarioWithFeaturedProduct(): void
    {
        // Define a product with features
        $colorFeature = new ProductFeatureType('Color', new UnconstrainedConstraint(FeatureValueType::TEXT));
        $sizeFeature = new ProductFeatureType('Size', new UnconstrainedConstraint(FeatureValueType::TEXT));
        $features = new ProductFeatureTypes([$colorFeature, $sizeFeature]);

        $productTypeId = UuidProductIdentifier::random();
        $productType = ProductType::identical(
            $productTypeId,
            ProductName::of('T-Shirt'),
            ProductDescription::of('Basic cotton t-shirt'),
            Unit::piece(),
            $features
        );

        $this->facade->defineProductType($productType);

        // Create instances with different feature values
        $retrievedType = $this->facade->getProductType($productTypeId);
        self::assertNotNull($retrievedType);

        $redShirtFeatures = new ProductFeatureInstances([
            new ProductFeatureInstance($colorFeature, 'Red'),
            new ProductFeatureInstance($sizeFeature, 'Large')
        ]);

        $blueShirtFeatures = new ProductFeatureInstances([
            new ProductFeatureInstance($colorFeature, 'Blue'),
            new ProductFeatureInstance($sizeFeature, 'Medium')
        ]);

        $redShirt = ProductInstance::builder()
            ->withId(ProductInstanceId::random())
            ->withProductType($retrievedType)
            ->withQuantity(10)
            ->withFeatures($redShirtFeatures)
            ->build();

        $blueShirt = ProductInstance::builder()
            ->withId(ProductInstanceId::random())
            ->withProductType($retrievedType)
            ->withQuantity(15)
            ->withFeatures($blueShirtFeatures)
            ->build();

        // Verify instances have different feature values
        self::assertEquals(2, $redShirt->features()->count());
        self::assertEquals(2, $blueShirt->features()->count());
        self::assertNotEquals($redShirt->features(), $blueShirt->features());
    }
}
