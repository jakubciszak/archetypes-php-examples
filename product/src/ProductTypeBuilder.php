<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product;

use SoftwareArchetypes\Product\Feature\ProductFeatureType;
use SoftwareArchetypes\Product\Feature\ProductFeatureTypes;
use SoftwareArchetypes\Product\Identifier\ProductIdentifier;

/**
 * Builder for creating ProductType instances with a fluent API.
 */
final class ProductTypeBuilder
{
    private ?ProductIdentifier $id = null;
    private ?ProductName $name = null;
    private ?ProductDescription $description = null;
    private ?Unit $preferredUnit = null;
    private ?ProductTrackingStrategy $trackingStrategy = null;
    /**
     * @var array<ProductFeatureType>
     */
    private array $featureTypes = [];

    public function withId(ProductIdentifier $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function withName(ProductName $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function withDescription(ProductDescription $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function withPreferredUnit(Unit $preferredUnit): self
    {
        $this->preferredUnit = $preferredUnit;
        return $this;
    }

    public function withTrackingStrategy(ProductTrackingStrategy $trackingStrategy): self
    {
        $this->trackingStrategy = $trackingStrategy;
        return $this;
    }

    public function withFeatureType(ProductFeatureType $featureType): self
    {
        $this->featureTypes[] = $featureType;
        return $this;
    }

    public function build(): ProductType
    {
        if ($this->id === null) {
            throw new \InvalidArgumentException('Product ID is required');
        }
        if ($this->name === null) {
            throw new \InvalidArgumentException('Product name is required');
        }
        if ($this->description === null) {
            throw new \InvalidArgumentException('Product description is required');
        }
        if ($this->preferredUnit === null) {
            throw new \InvalidArgumentException('Preferred unit is required');
        }
        if ($this->trackingStrategy === null) {
            throw new \InvalidArgumentException('Tracking strategy is required');
        }

        return new ProductType(
            $this->id,
            $this->name,
            $this->description,
            $this->preferredUnit,
            $this->trackingStrategy,
            new ProductFeatureTypes($this->featureTypes)
        );
    }
}
