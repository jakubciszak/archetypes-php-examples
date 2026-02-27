<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain;

use DateTimeImmutable;

/**
 * PromotionalAction represents various ways customers can earn bonus loyalty points
 * beyond standard purchases.
 *
 * Examples:
 * - App check-in series
 * - Extra points for specific products
 * - Quick pickup bonuses
 * - Seasonal promotions
 */
interface PromotionalAction
{
    public function actionId(): string;

    public function description(): string;

    public function calculateBonusPoints(): Points;

    public function isApplicable(DateTimeImmutable $date): bool;
}
