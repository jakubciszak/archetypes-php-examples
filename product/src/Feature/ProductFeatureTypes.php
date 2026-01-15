<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Feature;

use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

/**
 * Collection of ProductFeatureType instances.
 * Ensures uniqueness of feature type names and provides lookup capabilities.
 *
 * @implements IteratorAggregate<int, ProductFeatureType>
 */
final readonly class ProductFeatureTypes implements IteratorAggregate
{
    /**
     * @var array<string, ProductFeatureType>
     */
    private array $types;

    /**
     * @param array<ProductFeatureType> $types
     */
    public function __construct(array $types)
    {
        $indexed = [];
        foreach ($types as $type) {
            if (!$type instanceof ProductFeatureType) {
                throw new InvalidArgumentException(
                    'All elements must be instances of ProductFeatureType'
                );
            }

            $key = strtolower($type->name());
            if (isset($indexed[$key])) {
                throw new InvalidArgumentException(
                    'Duplicate feature type name: ' . $type->name()
                );
            }

            $indexed[$key] = $type;
        }
        $this->types = $indexed;
    }

    public function isEmpty(): bool
    {
        return empty($this->types);
    }

    public function count(): int
    {
        return count($this->types);
    }

    public function findByName(string $name): ?ProductFeatureType
    {
        $key = strtolower($name);
        return $this->types[$key] ?? null;
    }

    public function hasFeatureType(string $name): bool
    {
        return $this->findByName($name) !== null;
    }

    /**
     * @return array<ProductFeatureType>
     */
    public function toArray(): array
    {
        return array_values($this->types);
    }

    /**
     * @return Traversable<int, ProductFeatureType>
     */
    public function getIterator(): Traversable
    {
        yield from $this->toArray();
    }
}
