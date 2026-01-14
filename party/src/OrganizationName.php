<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party;

final readonly class OrganizationName
{
    public function __construct(private string $value)
    {
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
