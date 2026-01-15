<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product;

use SoftwareArchetypes\Product\Feature\ProductFeatureType;
use SoftwareArchetypes\Product\Feature\ProductFeatureTypes;
use SoftwareArchetypes\Product\Identifier\ProductIdentifier;

/**
 * ProductType aggregate root - defines a category or type of product.
 * Represents the product definition rather than specific instances.
 *
 * @internal The constructor is accessible for internal use by the builder but should not be called directly.
 * Use the static factory methods or builder() instead.
 */
final readonly class ProductType
{
    public function __construct(
        private ProductIdentifier $id,
        private ProductName $name,
        private ProductDescription $description,
        private Unit $preferredUnit,
        private ProductTrackingStrategy $trackingStrategy,
        private ProductFeatureTypes $featureTypes
    ) {
    }

    public static function unique(
        ProductIdentifier $id,
        ProductName $name,
        ProductDescription $description,
        Unit $preferredUnit,
        ?ProductFeatureTypes $featureTypes = null
    ): self {
        return new self(
            $id,
            $name,
            $description,
            $preferredUnit,
            ProductTrackingStrategy::UNIQUE,
            $featureTypes ?? new ProductFeatureTypes([])
        );
    }

    public static function individuallyTracked(
        ProductIdentifier $id,
        ProductName $name,
        ProductDescription $description,
        Unit $preferredUnit,
        ?ProductFeatureTypes $featureTypes = null
    ): self {
        return new self(
            $id,
            $name,
            $description,
            $preferredUnit,
            ProductTrackingStrategy::INDIVIDUALLY_TRACKED,
            $featureTypes ?? new ProductFeatureTypes([])
        );
    }

    public static function batchTracked(
        ProductIdentifier $id,
        ProductName $name,
        ProductDescription $description,
        Unit $preferredUnit,
        ?ProductFeatureTypes $featureTypes = null
    ): self {
        return new self(
            $id,
            $name,
            $description,
            $preferredUnit,
            ProductTrackingStrategy::BATCH_TRACKED,
            $featureTypes ?? new ProductFeatureTypes([])
        );
    }

    public static function individuallyAndBatchTracked(
        ProductIdentifier $id,
        ProductName $name,
        ProductDescription $description,
        Unit $preferredUnit,
        ?ProductFeatureTypes $featureTypes = null
    ): self {
        return new self(
            $id,
            $name,
            $description,
            $preferredUnit,
            ProductTrackingStrategy::INDIVIDUALLY_AND_BATCH_TRACKED,
            $featureTypes ?? new ProductFeatureTypes([])
        );
    }

    public static function identical(
        ProductIdentifier $id,
        ProductName $name,
        ProductDescription $description,
        Unit $preferredUnit,
        ?ProductFeatureTypes $featureTypes = null
    ): self {
        return new self(
            $id,
            $name,
            $description,
            $preferredUnit,
            ProductTrackingStrategy::IDENTICAL,
            $featureTypes ?? new ProductFeatureTypes([])
        );
    }

    public static function builder(): ProductTypeBuilder
    {
        return new ProductTypeBuilder();
    }

    public function id(): ProductIdentifier
    {
        return $this->id;
    }

    public function name(): ProductName
    {
        return $this->name;
    }

    public function description(): ProductDescription
    {
        return $this->description;
    }

    public function preferredUnit(): Unit
    {
        return $this->preferredUnit;
    }

    public function trackingStrategy(): ProductTrackingStrategy
    {
        return $this->trackingStrategy;
    }

    public function featureTypes(): ProductFeatureTypes
    {
        return $this->featureTypes;
    }
}
