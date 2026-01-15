<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Batch;

use InvalidArgumentException;

/**
 * Human-readable name for a batch (e.g., LOT-2025-001, BATCH-ABC-123).
 */
final readonly class BatchName
{
    private string $value;

    private function __construct(string $value)
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            throw new InvalidArgumentException('Batch name cannot be empty');
        }
        $this->value = $trimmed;
    }

    public static function of(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function asString(): string
    {
        return $this->value;
    }
}
