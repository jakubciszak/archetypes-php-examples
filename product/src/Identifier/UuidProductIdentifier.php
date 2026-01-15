<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Identifier;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

/**
 * Product identifier using UUID format.
 * Suitable for internal system-generated identifiers.
 */
final readonly class UuidProductIdentifier extends ProductIdentifier
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

    public function type(): string
    {
        return 'UUID';
    }
}
