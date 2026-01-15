<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Identifier;

/**
 * Abstract base class for product identifiers.
 * Product identifiers uniquely identify product types across systems.
 * Examples include: UUID, GTIN (barcode), ISBN (for books).
 */
abstract readonly class ProductIdentifier
{
    /**
     * Returns the string representation of the identifier.
     */
    abstract public function asString(): string;

    /**
     * Returns the type of identifier (e.g., 'UUID', 'GTIN', 'ISBN').
     */
    abstract public function type(): string;
}
