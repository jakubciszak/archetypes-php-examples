<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Pricing;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final readonly class CalculatorId
{
    public function __construct(private UuidInterface $id)
    {
    }

    public static function generate(): self
    {
        return new self(Uuid::uuid4());
    }

    public function toString(): string
    {
        return $this->id->toString();
    }
}
