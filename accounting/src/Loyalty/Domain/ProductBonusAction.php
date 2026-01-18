<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain;

use DateTimeImmutable;

/**
 * ProductBonusAction awards extra points for purchasing specific products.
 *
 * Example:
 * - Buy Product X, get 50 extra points
 * - Premium items get 2x points
 */
final readonly class ProductBonusAction implements PromotionalAction
{
    private function __construct(
        private string $actionId,
        private string $productId,
        private string $productName,
        private Points $bonusPoints,
        private DateTimeImmutable $validFrom,
        private DateTimeImmutable $validTo,
    ) {
    }

    public static function create(
        string $actionId,
        string $productId,
        string $productName,
        Points $bonusPoints,
        DateTimeImmutable $validFrom,
        DateTimeImmutable $validTo,
    ): self {
        return new self($actionId, $productId, $productName, $bonusPoints, $validFrom, $validTo);
    }

    public function actionId(): string
    {
        return $this->actionId;
    }

    public function description(): string
    {
        return sprintf(
            'Product bonus: %s (+%d points)',
            $this->productName,
            $this->bonusPoints->amount()
        );
    }

    public function productId(): string
    {
        return $this->productId;
    }

    public function productName(): string
    {
        return $this->productName;
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
