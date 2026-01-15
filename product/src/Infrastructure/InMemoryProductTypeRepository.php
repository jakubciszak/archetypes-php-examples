<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Infrastructure;

use SoftwareArchetypes\Product\Identifier\ProductIdentifier;
use SoftwareArchetypes\Product\ProductType;
use SoftwareArchetypes\Product\ProductTypeRepository;

/**
 * In-memory implementation of ProductTypeRepository.
 * Useful for testing and simple scenarios where no persistence is needed.
 */
final class InMemoryProductTypeRepository implements ProductTypeRepository
{
    /**
     * @var array<string, ProductType>
     */
    private array $productTypes = [];

    public function save(ProductType $productType): void
    {
        $this->productTypes[$productType->id()->asString()] = $productType;
    }

    public function findById(ProductIdentifier $id): ?ProductType
    {
        return $this->productTypes[$id->asString()] ?? null;
    }

    /**
     * @return array<ProductType>
     */
    public function findAll(): array
    {
        return array_values($this->productTypes);
    }
}
