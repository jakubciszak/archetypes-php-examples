<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product;

use InvalidArgumentException;
use SoftwareArchetypes\Product\Batch\BatchId;
use SoftwareArchetypes\Product\Feature\ProductFeatureInstances;
use SoftwareArchetypes\Product\SerialNumber\SerialNumber;

/**
 * ProductInstance represents a specific instance or occurrence of a ProductType.
 * While ProductType defines what a product is, ProductInstance represents actual items.
 *
 * @internal The constructor is accessible for internal use by the builder but should not be called directly.
 * Use the builder() method instead.
 */
final readonly class ProductInstance
{
    public function __construct(
        private ProductInstanceId $id,
        private ProductType $productType,
        private ?SerialNumber $serialNumber,
        private ?BatchId $batchId,
        private mixed $quantity,
        private ProductFeatureInstances $features
    ) {
        $this->validateTrackingRequirements();
    }

    public static function builder(): ProductInstanceBuilder
    {
        return new ProductInstanceBuilder();
    }

    public function id(): ProductInstanceId
    {
        return $this->id;
    }

    public function productType(): ProductType
    {
        return $this->productType;
    }

    public function serialNumber(): ?SerialNumber
    {
        return $this->serialNumber;
    }

    public function batchId(): ?BatchId
    {
        return $this->batchId;
    }

    public function quantity(): mixed
    {
        return $this->quantity;
    }

    public function features(): ProductFeatureInstances
    {
        return $this->features;
    }

    private function validateTrackingRequirements(): void
    {
        $strategy = $this->productType->trackingStrategy();

        if ($strategy->requiresBothTrackingMethods()) {
            if ($this->serialNumber === null || $this->batchId === null) {
                throw new InvalidArgumentException(
                    'Both serial number and batch ID are required '
                    . 'for products with INDIVIDUALLY_AND_BATCH_TRACKED strategy'
                );
            }
        } elseif ($strategy->isTrackedIndividually()) {
            if ($this->serialNumber === null) {
                throw new InvalidArgumentException(
                    'Serial number is required for individually tracked products'
                );
            }
        } elseif ($strategy->isTrackedByBatch()) {
            if ($this->batchId === null) {
                throw new InvalidArgumentException(
                    'Batch ID is required for batch tracked products'
                );
            }
        }
    }
}
