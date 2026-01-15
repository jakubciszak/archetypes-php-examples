<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Unique identifier for an individual product instance.
 */
final readonly class ProductInstanceId
{
    private function __construct(
        private UuidInterface $value
    ) {
    }

    public static function of(UuidInterface $value): self
    {
        return new self($value);
    }

    public static function random(): self
    {
        return new self(Uuid::uuid4());
    }

    public function value(): UuidInterface
    {
        return $this->value;
    }

    public function asString(): string
    {
        return $this->value->toString();
    }
}
