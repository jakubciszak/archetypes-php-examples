<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party;

use InvalidArgumentException;
use Ramsey\Uuid\UuidInterface;
use Ramsey\Uuid\Uuid;

final readonly class PartyId
{
    private UuidInterface $value;

    public function __construct(?UuidInterface $value)
    {
        if ($value === null) {
            throw new InvalidArgumentException('Party Id value cannot be null');
        }
        $this->value = $value;
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
