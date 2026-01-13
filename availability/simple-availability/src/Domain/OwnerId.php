<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Domain;

use JsonSerializable;
use Stringable;

final readonly class OwnerId implements JsonSerializable, Stringable
{
    private function __construct(
        private string $value
    ) {
    }

    public static function of(string $value): self
    {
        return new self($value);
    }

    public function asString(): string
    {
        return $this->value;
    }

    public function equals(?self $other): bool
    {
        return $other !== null && $this->value === $other->value;
    }

    public function jsonSerialize(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
