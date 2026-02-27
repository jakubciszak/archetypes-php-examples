<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain;

/**
 * Types of sub-accounts in the loyalty program ledger.
 *
 * Based on the Accounting archetype pattern from "Software Archetypes" (Chapter 7),
 * this implements the hierarchical account structure for tracking different states
 * of loyalty points.
 *
 * Account Hierarchy:
 * LoyaltyAccount (root)
 * ├── PendingFromPurchases - Points earned from purchases, awaiting activation
 * ├── PendingFromPromos - Points from promotions, awaiting activation
 * ├── ActivePoints - Points available for redemption
 * ├── SpentPoints - Points that have been redeemed
 * ├── ExpiredPoints - Points that expired before use
 * ├── ReversedPoints - Points reversed due to returns
 * └── AdjustmentPoints - Manual adjustments (corrections, compensations)
 */
enum AccountType: string
{
    case PENDING_FROM_PURCHASES = 'pending_from_purchases';
    case PENDING_FROM_PROMOS = 'pending_from_promos';
    case ACTIVE_POINTS = 'active_points';
    case SPENT_POINTS = 'spent_points';
    case EXPIRED_POINTS = 'expired_points';
    case REVERSED_POINTS = 'reversed_points';
    case ADJUSTMENT_POINTS = 'adjustment_points';

    public function isDebit(): bool
    {
        return match ($this) {
            self::PENDING_FROM_PURCHASES,
            self::PENDING_FROM_PROMOS,
            self::ACTIVE_POINTS => true,
            self::SPENT_POINTS,
            self::EXPIRED_POINTS,
            self::REVERSED_POINTS,
            self::ADJUSTMENT_POINTS => false,
        };
    }

    public function displayName(): string
    {
        return match ($this) {
            self::PENDING_FROM_PURCHASES => 'Pending from Purchases',
            self::PENDING_FROM_PROMOS => 'Pending from Promotions',
            self::ACTIVE_POINTS => 'Active Points',
            self::SPENT_POINTS => 'Spent Points',
            self::EXPIRED_POINTS => 'Expired Points',
            self::REVERSED_POINTS => 'Reversed Points',
            self::ADJUSTMENT_POINTS => 'Adjustment Points',
        };
    }
}
