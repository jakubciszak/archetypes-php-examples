<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain;

use SoftwareArchetypes\Accounting\Money;

/**
 * PostingRule defines how purchases are converted to loyalty points.
 * Based on the Accounting archetype pattern from "Software Archetypes" (Chapter 7).
 *
 * Different markets/stores have different conversion rates:
 * - e.g., Market PL: 1 PLN = 10 points
 * - e.g., Market DE: 1 EUR = 15 points
 */
final readonly class PostingRule
{
    private function __construct(
        private MarketId $marketId,
        private string $marketName,
        private int $pointsPerCurrencyUnit,
        private int $returnPeriodDays,
    ) {
        if ($pointsPerCurrencyUnit <= 0) {
            throw new \InvalidArgumentException('Points per currency unit must be positive');
        }
        if ($returnPeriodDays < 0) {
            throw new \InvalidArgumentException('Return period cannot be negative');
        }
    }

    public static function create(
        MarketId $marketId,
        string $marketName,
        int $pointsPerCurrencyUnit,
        int $returnPeriodDays,
    ): self {
        return new self($marketId, $marketName, $pointsPerCurrencyUnit, $returnPeriodDays);
    }

    public function marketId(): MarketId
    {
        return $this->marketId;
    }

    public function marketName(): string
    {
        return $this->marketName;
    }

    public function pointsPerCurrencyUnit(): int
    {
        return $this->pointsPerCurrencyUnit;
    }

    public function returnPeriodDays(): int
    {
        return $this->returnPeriodDays;
    }

    /**
     * Calculate points for a purchase amount.
     * Amount is in smallest currency unit (e.g., cents, groszy).
     */
    public function calculatePoints(Money $purchaseAmount): Points
    {
        // Convert smallest unit to main unit (e.g., cents to dollars)
        // Then multiply by points per unit
        $points = (int) (($purchaseAmount->amount() / 100) * $this->pointsPerCurrencyUnit);
        return Points::of($points);
    }
}
