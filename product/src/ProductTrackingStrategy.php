<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product;

/**
 * Defines how individual product instances are distinguished from each other.
 *
 * Answers the question: "How do we track and differentiate between items of this product type?"
 */
enum ProductTrackingStrategy
{
    /**
     * One-of-a-kind items (e.g., original artwork, prototypes).
     */
    case UNIQUE;

    /**
     * Each instance has unique identification (e.g., electronics with serial numbers).
     */
    case INDIVIDUALLY_TRACKED;

    /**
     * Grouped by production batch for quality control (e.g., pharmaceuticals, food products).
     */
    case BATCH_TRACKED;

    /**
     * Dual identification - both serial number and batch (e.g., medical devices requiring full traceability).
     */
    case INDIVIDUALLY_AND_BATCH_TRACKED;

    /**
     * Interchangeable items without individual tracking (e.g., commodity products, bulk materials).
     */
    case IDENTICAL;

    /**
     * Indicates if this strategy requires tracking individual items.
     */
    public function isTrackedIndividually(): bool
    {
        return match ($this) {
            self::UNIQUE,
            self::INDIVIDUALLY_TRACKED,
            self::INDIVIDUALLY_AND_BATCH_TRACKED => true,
            default => false,
        };
    }

    /**
     * Indicates if this strategy requires batch tracking.
     */
    public function isTrackedByBatch(): bool
    {
        return match ($this) {
            self::BATCH_TRACKED,
            self::INDIVIDUALLY_AND_BATCH_TRACKED => true,
            default => false,
        };
    }

    /**
     * Indicates if this strategy requires both individual and batch tracking.
     */
    public function requiresBothTrackingMethods(): bool
    {
        return $this === self::INDIVIDUALLY_AND_BATCH_TRACKED;
    }

    /**
     * Indicates if items are interchangeable (no tracking required).
     */
    public function isInterchangeable(): bool
    {
        return $this === self::IDENTICAL;
    }
}
