<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain;

use DateTimeImmutable;

/**
 * PendingPoints represents points that are earned but not yet activated.
 * Points remain pending until the return period expires for the purchase.
 *
 * This prevents points from being used before customers can return products.
 */
final class PendingPoints
{
    private bool $activated = false;
    private bool $reversed = false;

    private function __construct(
        private readonly PurchaseId $purchaseId,
        private readonly Points $points,
        private readonly DateTimeImmutable $purchaseDate,
        private readonly DateTimeImmutable $activationDate,
        private readonly string $reason,
    ) {
    }

    public static function forPurchase(
        PurchaseId $purchaseId,
        Points $points,
        DateTimeImmutable $purchaseDate,
        DateTimeImmutable $activationDate,
        string $reason = 'Purchase',
    ): self {
        if ($activationDate < $purchaseDate) {
            throw new \InvalidArgumentException('Activation date cannot be before purchase date');
        }
        return new self($purchaseId, $points, $purchaseDate, $activationDate, $reason);
    }

    public function purchaseId(): PurchaseId
    {
        return $this->purchaseId;
    }

    public function points(): Points
    {
        return $this->points;
    }

    public function purchaseDate(): DateTimeImmutable
    {
        return $this->purchaseDate;
    }

    public function activationDate(): DateTimeImmutable
    {
        return $this->activationDate;
    }

    public function reason(): string
    {
        return $this->reason;
    }

    public function isActivated(): bool
    {
        return $this->activated;
    }

    public function isReversed(): bool
    {
        return $this->reversed;
    }

    public function canActivate(DateTimeImmutable $currentDate): bool
    {
        return !$this->activated
            && !$this->reversed
            && $currentDate >= $this->activationDate;
    }

    public function activate(): void
    {
        if ($this->activated) {
            throw new \RuntimeException('Points already activated');
        }
        if ($this->reversed) {
            throw new \RuntimeException('Cannot activate reversed points');
        }
        $this->activated = true;
    }

    public function reverse(): void
    {
        if ($this->reversed) {
            throw new \RuntimeException('Points already reversed');
        }
        if ($this->activated) {
            throw new \RuntimeException('Cannot reverse activated points');
        }
        $this->reversed = true;
    }
}
