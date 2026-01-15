<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Feature;

use InvalidArgumentException;
use IteratorAggregate;
use Traversable;

/**
 * Collection of ProductFeatureInstance instances.
 * Ensures uniqueness of feature names and provides lookup capabilities.
 *
 * @implements IteratorAggregate<int, ProductFeatureInstance>
 */
final readonly class ProductFeatureInstances implements IteratorAggregate
{
    /**
     * @var array<string, ProductFeatureInstance>
     */
    private array $instances;

    /**
     * @param array<ProductFeatureInstance> $instances
     */
    public function __construct(array $instances)
    {
        $indexed = [];
        foreach ($instances as $instance) {
            if (!$instance instanceof ProductFeatureInstance) {
                throw new InvalidArgumentException(
                    'All elements must be instances of ProductFeatureInstance'
                );
            }

            $key = strtolower($instance->name());
            if (isset($indexed[$key])) {
                throw new InvalidArgumentException(
                    'Duplicate feature name: ' . $instance->name()
                );
            }

            $indexed[$key] = $instance;
        }
        $this->instances = $indexed;
    }

    public function isEmpty(): bool
    {
        return empty($this->instances);
    }

    public function count(): int
    {
        return count($this->instances);
    }

    public function findByName(string $name): ?ProductFeatureInstance
    {
        $key = strtolower($name);
        return $this->instances[$key] ?? null;
    }

    public function hasFeature(string $name): bool
    {
        return $this->findByName($name) !== null;
    }

    /**
     * @return array<ProductFeatureInstance>
     */
    public function toArray(): array
    {
        return array_values($this->instances);
    }

    /**
     * @return Traversable<int, ProductFeatureInstance>
     */
    public function getIterator(): Traversable
    {
        yield from $this->toArray();
    }
}
