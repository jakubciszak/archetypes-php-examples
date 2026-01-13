<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability\Application;

use SoftwareArchetypes\Availability\TimedAvailability\Domain\Owner;
use SoftwareArchetypes\Availability\TimedAvailability\Domain\ResourceId;
use SoftwareArchetypes\Availability\TimedAvailability\Domain\TimeSlot;

final readonly class Calendar
{
    /**
     * @param array<string, list<TimeSlot>> $calendar
     */
    public function __construct(
        public ResourceId $resourceId,
        public array $calendar
    ) {
    }

    /**
     * @param list<TimeSlot> $availableSlots
     */
    public static function withAvailableSlots(ResourceId $resourceId, array $availableSlots): self
    {
        return new self($resourceId, ['none' => $availableSlots]);
    }

    public static function empty(ResourceId $resourceId): self
    {
        return new self($resourceId, []);
    }

    /**
     * @return list<TimeSlot>
     */
    public function availableSlots(): array
    {
        return $this->calendar['none'] ?? [];
    }

    /**
     * @return list<TimeSlot>
     */
    public function takenBy(Owner $requester): array
    {
        $key = $requester->id()?->toString() ?? 'none';
        return $this->calendar[$key] ?? [];
    }
}
