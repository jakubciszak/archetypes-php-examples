<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability;

final readonly class Calendars
{
    /**
     * @param array<string, Calendar> $calendars
     */
    public function __construct(private array $calendars)
    {
    }

    /**
     * @param list<Calendar> $calendars
     */
    public static function of(array $calendars): self
    {
        $mapped = [];
        foreach ($calendars as $calendar) {
            $key = $calendar->resourceId->getId()?->toString() ?? 'none';
            $mapped[$key] = $calendar;
        }

        return new self($mapped);
    }

    public function get(ResourceId $resourceId): Calendar
    {
        $key = $resourceId->getId()?->toString() ?? 'none';
        return $this->calendars[$key] ?? Calendar::empty($resourceId);
    }
}
