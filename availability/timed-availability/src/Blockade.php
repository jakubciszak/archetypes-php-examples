<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability;

final readonly class Blockade
{
    private function __construct(
        private Owner $takenBy,
        private bool $disabled
    ) {
    }

    public static function none(): self
    {
        return new self(Owner::none(), false);
    }

    public static function disabledBy(Owner $owner): self
    {
        return new self($owner, true);
    }

    public static function ownedBy(Owner $owner): self
    {
        return new self($owner, false);
    }

    public function takenBy(): Owner
    {
        return $this->takenBy;
    }

    public function disabled(): bool
    {
        return $this->disabled;
    }

    public function canBeTakenBy(Owner $requester): bool
    {
        return $this->takenBy->byNone() || $this->takenBy->equals($requester);
    }

    public function isDisabledBy(Owner $owner): bool
    {
        return $this->disabled && $owner->equals($this->takenBy);
    }
}
