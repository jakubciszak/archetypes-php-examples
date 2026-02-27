<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain;

use DateTimeImmutable;

/**
 * CheckInSeriesAction awards points for consecutive daily app check-ins.
 *
 * Example:
 * - 1st check-in: 10 points
 * - 2nd consecutive: 20 points
 * - 3rd consecutive: 30 points
 * - 7th consecutive: 100 points
 */
final readonly class CheckInSeriesAction implements PromotionalAction
{
    private function __construct(
        private string $actionId,
        private int $consecutiveDays,
        private Points $bonusPoints,
        private DateTimeImmutable $validFrom,
        private DateTimeImmutable $validTo,
    ) {
        if ($consecutiveDays <= 0) {
            throw new \InvalidArgumentException('Consecutive days must be positive');
        }
    }

    public static function create(
        string $actionId,
        int $consecutiveDays,
        Points $bonusPoints,
        DateTimeImmutable $validFrom,
        DateTimeImmutable $validTo,
    ): self {
        return new self($actionId, $consecutiveDays, $bonusPoints, $validFrom, $validTo);
    }

    public function actionId(): string
    {
        return $this->actionId;
    }

    public function description(): string
    {
        return sprintf(
            'Check-in series: %d consecutive days',
            $this->consecutiveDays
        );
    }

    public function consecutiveDays(): int
    {
        return $this->consecutiveDays;
    }

    public function calculateBonusPoints(): Points
    {
        return $this->bonusPoints;
    }

    public function isApplicable(DateTimeImmutable $date): bool
    {
        return $date >= $this->validFrom && $date <= $this->validTo;
    }
}
