<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party\Common;

final readonly class Version
{
    private function __construct(private int $value)
    {
    }

    public static function initial(): self
    {
        return new self(0);
    }

    public static function of(int $value): self
    {
        return new self($value);
    }

    public function next(): self
    {
        return new self($this->value + 1);
    }

    public function value(): int
    {
        return $this->value;
    }
}
