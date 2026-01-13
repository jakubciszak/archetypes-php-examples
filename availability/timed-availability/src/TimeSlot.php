<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;

final readonly class TimeSlot
{
    public function __construct(
        private DateTimeImmutable $from,
        private DateTimeImmutable $to
    ) {
    }

    public static function empty(): self
    {
        $epoch = new DateTimeImmutable('@0');
        return new self($epoch, $epoch);
    }

    public static function createDailyTimeSlotAtUTC(int $year, int $month, int $day): self
    {
        $from = new DateTimeImmutable(
            sprintf('%04d-%02d-%02d 00:00:00', $year, $month, $day),
            new DateTimeZone('UTC')
        );
        $to = $from->add(new DateInterval('P1D'));

        return new self($from, $to);
    }

    public static function createMonthlyTimeSlotAtUTC(int $year, int $month): self
    {
        $from = new DateTimeImmutable(
            sprintf('%04d-%02d-01 00:00:00', $year, $month),
            new DateTimeZone('UTC')
        );
        $to = $from->add(new DateInterval('P1M'));

        return new self($from, $to);
    }

    public function from(): DateTimeImmutable
    {
        return $this->from;
    }

    public function to(): DateTimeImmutable
    {
        return $this->to;
    }

    public function overlapsWith(self $other): bool
    {
        return $this->from < $other->to && $this->to > $other->from;
    }

    public function within(self $other): bool
    {
        return !($this->from < $other->from) && !($this->to > $other->to);
    }

    /**
     * @return list<TimeSlot>
     */
    public function leftoverAfterRemovingCommonWith(self $other): array
    {
        if ($this->equals($other)) {
            return [];
        }

        if (!$this->overlapsWith($other)) {
            return [$this, $other];
        }

        $result = [];

        if ($this->from < $other->from) {
            $result[] = new self($this->from, $other->from);
        }

        if ($other->from < $this->from) {
            $result[] = new self($other->from, $this->from);
        }

        if ($this->to > $other->to) {
            $result[] = new self($other->to, $this->to);
        }

        if ($other->to > $this->to) {
            $result[] = new self($this->to, $other->to);
        }

        return $result;
    }

    public function commonPartWith(self $other): self
    {
        if (!$this->overlapsWith($other)) {
            return self::empty();
        }

        $commonStart = $this->from > $other->from ? $this->from : $other->from;
        $commonEnd = $this->to < $other->to ? $this->to : $other->to;

        return new self($commonStart, $commonEnd);
    }

    public function isEmpty(): bool
    {
        return $this->from == $this->to;
    }

    public function duration(): DateInterval
    {
        return $this->from->diff($this->to);
    }

    public function stretch(DateInterval $duration): self
    {
        return new self(
            $this->from->sub($duration),
            $this->to->add($duration)
        );
    }

    public function equals(self $other): bool
    {
        return $this->from == $other->from && $this->to == $other->to;
    }
}
