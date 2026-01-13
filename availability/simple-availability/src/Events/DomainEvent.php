<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Events;

use DateTimeImmutable;

interface DomainEvent
{
    public function getId(): string;

    public function getOccurredAt(): DateTimeImmutable;

    public function getType(): string;
}
