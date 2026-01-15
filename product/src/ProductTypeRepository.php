<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product;

use SoftwareArchetypes\Product\Identifier\ProductIdentifier;

/**
 * Repository interface for ProductType aggregate.
 * Provides persistence operations for product type definitions.
 */
interface ProductTypeRepository
{
    /**
     * Saves a product type to the repository.
     */
    public function save(ProductType $productType): void;

    /**
     * Finds a product type by its identifier.
     * Returns null if not found.
     */
    public function findById(ProductIdentifier $id): ?ProductType;

    /**
     * Finds all product types.
     *
     * @return array<ProductType>
     */
    public function findAll(): array;
}
