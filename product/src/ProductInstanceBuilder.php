<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product;

use InvalidArgumentException;
use SoftwareArchetypes\Product\Batch\BatchId;
use SoftwareArchetypes\Product\Feature\ProductFeatureInstances;
use SoftwareArchetypes\Product\SerialNumber\SerialNumber;

/**
 * Builder for creating ProductInstance instances with a fluent API.
 */
final class ProductInstanceBuilder
{
    private ?ProductInstanceId $id = null;
    private ?ProductType $productType = null;
    private ?SerialNumber $serialNumber = null;
    private ?BatchId $batchId = null;
    private mixed $quantity = null;
    private ?ProductFeatureInstances $features = null;

    public function withId(ProductInstanceId $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function withProductType(ProductType $productType): self
    {
        $this->productType = $productType;
        return $this;
    }

    public function withSerialNumber(?SerialNumber $serialNumber): self
    {
        $this->serialNumber = $serialNumber;
        return $this;
    }

    public function withBatchId(?BatchId $batchId): self
    {
        $this->batchId = $batchId;
        return $this;
    }

    public function withQuantity(mixed $quantity): self
    {
        $this->quantity = $quantity;
        return $this;
    }

    public function withFeatures(ProductFeatureInstances $features): self
    {
        $this->features = $features;
        return $this;
    }

    public function build(): ProductInstance
    {
        if ($this->id === null) {
            throw new InvalidArgumentException('Product instance ID is required');
        }
        if ($this->productType === null) {
            throw new InvalidArgumentException('Product type is required');
        }

        return new ProductInstance(
            $this->id,
            $this->productType,
            $this->serialNumber,
            $this->batchId,
            $this->quantity,
            $this->features ?? new ProductFeatureInstances([])
        );
    }
}
