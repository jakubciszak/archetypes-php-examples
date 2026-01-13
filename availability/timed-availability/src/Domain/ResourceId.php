<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability\Domain;

use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final readonly class ResourceId
{
    private function __construct(private ?UuidInterface $id)
    {
    }

    public static function newOne(): self
    {
        return new self(Uuid::uuid4());
    }

    public static function none(): self
    {
        return new self(null);
    }

    public static function of(string $id): self
    {
        return new self(Uuid::fromString($id));
    }

    public static function fromUuid(UuidInterface $uuid): self
    {
        return new self($uuid);
    }

    public function getId(): ?UuidInterface
    {
        return $this->id;
    }

    public function equals(self $other): bool
    {
        if ($this->id === null && $other->id === null) {
            return true;
        }

        if ($this->id === null || $other->id === null) {
            return false;
        }

        return $this->id->equals($other->id);
    }
}
