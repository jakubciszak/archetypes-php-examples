<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product;

use DateTimeImmutable;
use InvalidArgumentException;

/**
 * Represents a time period during which something is valid.
 * Used for product availability, pricing periods, feature availability, etc.
 */
final readonly class Validity
{
    public function __construct(
        private DateTimeImmutable $from,
        private ?DateTimeImmutable $to = null
    ) {
        if ($this->to !== null && $this->to < $this->from) {
            throw new InvalidArgumentException(
                'To date cannot be before from date'
            );
        }
    }

    public function from(): DateTimeImmutable
    {
        return $this->from;
    }

    public function to(): ?DateTimeImmutable
    {
        return $this->to;
    }

    /**
     * Checks if a given date falls within this validity period.
     */
    public function isValidAt(DateTimeImmutable $date): bool
    {
        if ($date < $this->from) {
            return false;
        }

        if ($this->to !== null && $date > $this->to) {
            return false;
        }

        return true;
    }

    /**
     * Checks if the validity period includes the current moment.
     */
    public function isCurrentlyValid(): bool
    {
        return $this->isValidAt(new DateTimeImmutable());
    }
}
