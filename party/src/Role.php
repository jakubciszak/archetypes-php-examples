<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party;

use InvalidArgumentException;

final readonly class Role
{
    private string $name;

    public function __construct(?string $name)
    {
        if ($name === null || trim($name) === '') {
            throw new InvalidArgumentException('Role name cannot be blank');
        }
        $this->name = $name;
    }

    public static function of(string $value): self
    {
        return new self($value);
    }

    public function name(): string
    {
        return $this->name;
    }

    public function asString(): string
    {
        return $this->name;
    }
}
