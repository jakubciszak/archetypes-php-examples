<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\SimpleAvailability\Tests\Support;

use DateInterval;
use DateTimeImmutable;
use SoftwareArchetypes\Availability\SimpleAvailability\Common\Clock;

final class MockClock implements Clock
{
    private DateTimeImmutable $currentTime;

    public function __construct(?DateTimeImmutable $currentTime = null)
    {
        $this->currentTime = $currentTime ?? new DateTimeImmutable();
    }

    public function now(): DateTimeImmutable
    {
        return $this->currentTime;
    }

    public function setTime(DateTimeImmutable $time): void
    {
        $this->currentTime = $time;
    }

    public function daysForward(int $days): self
    {
        $this->currentTime = $this->currentTime->add(new DateInterval("P{$days}D"));
        return $this;
    }

    public function daysBackward(int $days): self
    {
        $this->currentTime = $this->currentTime->sub(new DateInterval("P{$days}D"));
        return $this;
    }

    public function hoursForward(int $hours): self
    {
        $this->currentTime = $this->currentTime->add(new DateInterval("PT{$hours}H"));
        return $this;
    }

    public function hoursBackward(int $hours): self
    {
        $this->currentTime = $this->currentTime->sub(new DateInterval("PT{$hours}H"));
        return $this;
    }

    public function minutesForward(int $minutes): self
    {
        $this->currentTime = $this->currentTime->add(new DateInterval("PT{$minutes}M"));
        return $this;
    }

    public function minutesBackward(int $minutes): self
    {
        $this->currentTime = $this->currentTime->sub(new DateInterval("PT{$minutes}M"));
        return $this;
    }
}
