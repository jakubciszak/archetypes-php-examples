<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Events;

use DateTimeImmutable;

abstract readonly class BaseDomainEvent implements DomainEvent
{
    public function __construct(
        private string $id,
        private DateTimeImmutable $occurredAt
    ) {
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getOccurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }

    abstract public function getType(): string;

    protected static function generateId(): string
    {
        return bin2hex(random_bytes(16));
    }

    protected static function now(?\SoftwareArchetypes\Availability\SimpleAvailability\Common\Clock $clock = null): DateTimeImmutable
    {
        if ($clock === null) {
            return new DateTimeImmutable();
        }
        return $clock->now();
    }
}
