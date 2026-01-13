<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final readonly class Owner
{
    private function __construct(private ?UuidInterface $owner)
    {
    }

    public static function none(): self
    {
        return new self(null);
    }

    public static function newOne(): self
    {
        return new self(Uuid::uuid4());
    }

    public static function of(UuidInterface $id): self
    {
        return new self($id);
    }

    public function byNone(): bool
    {
        return $this->owner === null;
    }

    public function id(): ?UuidInterface
    {
        return $this->owner;
    }

    public function equals(self $other): bool
    {
        if ($this->owner === null && $other->owner === null) {
            return true;
        }

        if ($this->owner === null || $other->owner === null) {
            return false;
        }

        return $this->owner->equals($other->owner);
    }
}
