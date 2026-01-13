<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability\Events;

use DateTimeImmutable;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;
use SoftwareArchetypes\Availability\TimedAvailability\Domain\Owner;
use SoftwareArchetypes\Availability\TimedAvailability\Domain\ResourceId;
use SoftwareArchetypes\Availability\TimedAvailability\Domain\TimeSlot;

final readonly class ResourceTakenOver implements PublishedEvent
{
    /**
     * @param list<Owner> $previousOwners
     */
    public function __construct(
        public UuidInterface $eventId,
        public ResourceId $resourceId,
        public array $previousOwners,
        public TimeSlot $slot,
        public DateTimeImmutable $occurredAt
    ) {
    }

    /**
     * @param list<Owner> $previousOwners
     */
    public static function now(ResourceId $resourceId, array $previousOwners, TimeSlot $slot): self
    {
        return new self(
            Uuid::uuid4(),
            $resourceId,
            $previousOwners,
            $slot,
            new DateTimeImmutable()
        );
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
