<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Batch;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Represents a batch of products manufactured together.
 * Batches are used for quality control, traceability, and recalls.
 */
final readonly class Batch
{
    public function __construct(
        private BatchId $id,
        private BatchName $name,
        private ?DateTimeImmutable $productionDate = null,
        private ?DateTimeImmutable $expiryDate = null
    ) {
        if ($this->productionDate !== null && $this->expiryDate !== null) {
            if ($this->expiryDate < $this->productionDate) {
                throw new InvalidArgumentException(
                    'Expiry date cannot be before production date'
                );
            }
        }
    }

    public function id(): BatchId
    {
        return $this->id;
    }

    public function name(): BatchName
    {
        return $this->name;
    }

    public function productionDate(): ?DateTimeImmutable
    {
        return $this->productionDate;
    }

    public function expiryDate(): ?DateTimeImmutable
    {
        return $this->expiryDate;
    }
}
