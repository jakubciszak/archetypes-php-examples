<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain;

use SoftwareArchetypes\Accounting\Money;

/**
 * AccountingPractice bundles market-specific rules for the loyalty program.
 *
 * Based on the Accounting archetype pattern from "Software Archetypes" (Chapter 7).
 *
 * This encapsulates:
 * - Points conversion ratios (how much money = how many points)
 * - Pending maturation periods (return window)
 * - Rounding rules
 * - Promotional conditions
 * - Expiration periods
 *
 * Different markets can have different practices without code changes.
 */
final readonly class AccountingPractice
{
    /**
     * @param array<string, float> $promotionalMultipliers
     */
    public function __construct(
        private MarketId $marketId,
        private string $marketName,
        private int $pointsPerCurrencyUnit,
        private int $maturationPeriodDays,
        private int $pointsExpirationDays,
        private bool $roundDown,
        private array $promotionalMultipliers = [],
    ) {
        if ($pointsPerCurrencyUnit <= 0) {
            throw new \InvalidArgumentException('Points per currency unit must be positive');
        }
        if ($maturationPeriodDays < 0) {
            throw new \InvalidArgumentException('Maturation period cannot be negative');
        }
        if ($pointsExpirationDays < 0) {
            throw new \InvalidArgumentException('Expiration period cannot be negative');
        }
    }

    /**
     * @param array<string, float> $promotionalMultipliers
     */
    public static function forMarket(
        MarketId $marketId,
        string $marketName,
        int $pointsPerCurrencyUnit,
        int $maturationPeriodDays,
        int $pointsExpirationDays = 365,
        bool $roundDown = true,
        array $promotionalMultipliers = [],
    ): self {
        return new self(
            $marketId,
            $marketName,
            $pointsPerCurrencyUnit,
            $maturationPeriodDays,
            $pointsExpirationDays,
            $roundDown,
            $promotionalMultipliers,
        );
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

    public function maturationPeriodDays(): int
    {
        return $this->maturationPeriodDays;
    }

    public function pointsExpirationDays(): int
    {
        return $this->pointsExpirationDays;
    }

    public function roundDown(): bool
    {
        return $this->roundDown;
    }

    /**
     * Calculate points for a purchase amount.
     * Amount is in smallest currency unit (e.g., cents, groszy).
     */
    public function calculatePoints(Money $purchaseAmount, ?string $productId = null): Points
    {
        // Convert smallest unit to main unit (e.g., cents to dollars)
        $mainUnits = $purchaseAmount->amount() / 100.0;

        // Apply base conversion
        $rawPoints = $mainUnits * $this->pointsPerCurrencyUnit;

        // Apply promotional multiplier if applicable
        if ($productId) {
            $rawPoints *= $this->promotionalMultiplier($productId);
        }

        // Apply rounding
        $points = $this->roundDown
            ? (int) floor($rawPoints)
            : (int) round($rawPoints);

        return Points::of(max(0, $points));
    }

    /**
     * Get promotional multiplier for a product.
     */
    public function promotionalMultiplier(string $productId): float
    {
        return $this->promotionalMultipliers[$productId] ?? 1.0;
    }

    /**
     * Check if a product has a promotional multiplier.
     */
    public function hasPromotionalMultiplier(string $productId): bool
    {
        return isset($this->promotionalMultipliers[$productId]);
    }
}
