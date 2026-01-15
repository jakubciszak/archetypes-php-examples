<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Application;

use SoftwareArchetypes\Product\Identifier\ProductIdentifier;
use SoftwareArchetypes\Product\ProductType;
use SoftwareArchetypes\Product\ProductTypeRepository;

/**
 * Application facade for product management.
 * Provides a simplified API for product operations.
 */
final class ProductFacade
{
    public function __construct(
        private readonly ProductTypeRepository $productTypeRepository
    ) {
    }

    /**
     * Defines a new product type.
     */
    public function defineProductType(ProductType $productType): void
    {
        $this->productTypeRepository->save($productType);
    }

    /**
     * Retrieves a product type by its identifier.
     */
    public function getProductType(ProductIdentifier $id): ?ProductType
    {
        return $this->productTypeRepository->findById($id);
    }

    /**
     * Retrieves all product types.
     *
     * @return array<ProductType>
     */
    public function getAllProductTypes(): array
    {
        return $this->productTypeRepository->findAll();
    }

    /**
     * Checks if a product type exists.
     */
    public function productTypeExists(ProductIdentifier $id): bool
    {
        return $this->productTypeRepository->findById($id) !== null;
    }
}
