<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain;

use DateTimeImmutable;

/**
 * QuickPickupAction awards extra points for picking up packages quickly.
 *
 * Example:
 * - Pickup within 24 hours: 50 points
 * - Pickup within 48 hours: 25 points
 */
final readonly class QuickPickupAction implements PromotionalAction
{
    private function __construct(
        private string $actionId,
        private int $maxHoursForBonus,
        private Points $bonusPoints,
        private DateTimeImmutable $validFrom,
        private DateTimeImmutable $validTo,
    ) {
        if ($maxHoursForBonus <= 0) {
            throw new \InvalidArgumentException('Max hours for bonus must be positive');
        }
    }

    public static function create(
        string $actionId,
        int $maxHoursForBonus,
        Points $bonusPoints,
        DateTimeImmutable $validFrom,
        DateTimeImmutable $validTo,
    ): self {
        return new self($actionId, $maxHoursForBonus, $bonusPoints, $validFrom, $validTo);
    }

    public function actionId(): string
    {
        return $this->actionId;
    }

    public function description(): string
    {
        return sprintf(
            'Quick pickup: within %d hours (+%d points)',
            $this->maxHoursForBonus,
            $this->bonusPoints->amount()
        );
    }

    public function maxHoursForBonus(): int
    {
        return $this->maxHoursForBonus;
    }

    public function calculateBonusPoints(): Points
    {
        return $this->bonusPoints;
    }

    public function isApplicable(DateTimeImmutable $date): bool
    {
        return $date >= $this->validFrom && $date <= $this->validTo;
    }

    public function isPickupQualifying(
        DateTimeImmutable $orderDate,
        DateTimeImmutable $pickupDate,
    ): bool {
        $hoursDiff = ($pickupDate->getTimestamp() - $orderDate->getTimestamp()) / 3600;
        return $hoursDiff <= $this->maxHoursForBonus;
    }
}
