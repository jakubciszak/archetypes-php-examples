<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Loyalty\Domain;

use DateTimeImmutable;
use SoftwareArchetypes\Accounting\Loyalty\Events\LoyaltyEvent;
use SoftwareArchetypes\Accounting\Loyalty\Events\PointsActivated;
use SoftwareArchetypes\Accounting\Loyalty\Events\PointsEarned;
use SoftwareArchetypes\Accounting\Loyalty\Events\PointsReversed;
use SoftwareArchetypes\Accounting\Loyalty\Events\PromotionalPointsAwarded;
use SoftwareArchetypes\Accounting\Money;

/**
 * LoyaltyAccount is the aggregate root for loyalty program management.
 *
 * Based on the Accounting archetype pattern, it manages:
 * - Active points (ready to use)
 * - Pending points (awaiting activation after return period)
 * - Purchase history
 * - Promotional bonuses
 */
final class LoyaltyAccount
{
    private Points $activePoints;

    /**
     * @var array<string, PendingPoints>
     */
    private array $pendingPoints = [];

    /**
     * @var list<LoyaltyEvent>
     */
    private array $pendingEvents = [];

    private function __construct(
        private readonly LoyaltyAccountId $accountId,
        private readonly string $customerId,
        private readonly string $customerName,
    ) {
        $this->activePoints = Points::zero();
    }

    public static function create(
        LoyaltyAccountId $accountId,
        string $customerId,
        string $customerName,
    ): self {
        return new self($accountId, $customerId, $customerName);
    }

    public function id(): LoyaltyAccountId
    {
        return $this->accountId;
    }

    public function customerId(): string
    {
        return $this->customerId;
    }

    public function customerName(): string
    {
        return $this->customerName;
    }

    public function activePoints(): Points
    {
        return $this->activePoints;
    }

    public function totalPendingPoints(): Points
    {
        $total = Points::zero();
        foreach ($this->pendingPoints as $pending) {
            if (!$pending->isReversed() && !$pending->isActivated()) {
                $total = $total->add($pending->points());
            }
        }
        return $total;
    }

    /**
     * @return list<PendingPoints>
     */
    public function pendingPointsList(): array
    {
        return array_values($this->pendingPoints);
    }

    /**
     * Record a purchase and calculate pending points based on posting rule.
     */
    public function recordPurchase(
        PurchaseId $purchaseId,
        Money $purchaseAmount,
        PostingRule $postingRule,
        DateTimeImmutable $purchaseDate,
    ): void {
        // Calculate points based on posting rule
        $points = $postingRule->calculatePoints($purchaseAmount);

        if ($points->isZero()) {
            return; // No points to award
        }

        // Calculate activation date based on return period
        $activationDate = $purchaseDate->modify(
            sprintf('+%d days', $postingRule->returnPeriodDays())
        );

        // Create pending points
        $pending = PendingPoints::forPurchase(
            $purchaseId,
            $points,
            $purchaseDate,
            $activationDate,
            sprintf('Purchase in %s', $postingRule->marketName()),
        );

        $this->pendingPoints[$purchaseId->toString()] = $pending;

        // Record event
        $this->pendingEvents[] = new PointsEarned(
            $this->accountId,
            $purchaseId,
            $points,
            $purchaseDate,
            $activationDate,
            $postingRule->marketId(),
        );
    }

    /**
     * Award promotional bonus points.
     */
    public function awardPromotionalPoints(
        PromotionalAction $action,
        DateTimeImmutable $awardDate,
    ): void {
        if (!$action->isApplicable($awardDate)) {
            throw new \InvalidArgumentException('Promotional action is not applicable on this date');
        }

        $bonusPoints = $action->calculateBonusPoints();

        if ($bonusPoints->isZero()) {
            return;
        }

        // Promotional points are immediately active (no pending period)
        $this->activePoints = $this->activePoints->add($bonusPoints);

        $this->pendingEvents[] = new PromotionalPointsAwarded(
            $this->accountId,
            $action->actionId(),
            $action->description(),
            $bonusPoints,
            $awardDate,
        );
    }

    /**
     * Activate pending points that have passed their activation date.
     */
    public function activatePendingPoints(DateTimeImmutable $currentDate): void
    {
        foreach ($this->pendingPoints as $pending) {
            if ($pending->canActivate($currentDate)) {
                $pending->activate();
                $this->activePoints = $this->activePoints->add($pending->points());

                $this->pendingEvents[] = new PointsActivated(
                    $this->accountId,
                    $pending->purchaseId(),
                    $pending->points(),
                    $currentDate,
                );
            }
        }
    }

    /**
     * Reverse points for a returned purchase.
     */
    public function reversePurchase(
        PurchaseId $purchaseId,
        DateTimeImmutable $returnDate,
    ): void {
        $purchaseKey = $purchaseId->toString();

        if (!isset($this->pendingPoints[$purchaseKey])) {
            throw new \InvalidArgumentException(
                sprintf('Purchase %s not found', $purchaseId->toString())
            );
        }

        $pending = $this->pendingPoints[$purchaseKey];

        if ($pending->isActivated()) {
            // Points already activated - deduct from active points
            $this->activePoints = $this->activePoints->subtract($pending->points());
        } elseif (!$pending->isReversed()) {
            // Points still pending - just mark as reversed
            $pending->reverse();
        }

        $this->pendingEvents[] = new PointsReversed(
            $this->accountId,
            $purchaseId,
            $pending->points(),
            $returnDate,
        );
    }

    /**
     * Use points (e.g., for redemption).
     */
    public function usePoints(Points $points): void
    {
        if ($this->activePoints->compareTo($points) < 0) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Insufficient points. Available: %d, Required: %d',
                    $this->activePoints->amount(),
                    $points->amount()
                )
            );
        }

        $this->activePoints = $this->activePoints->subtract($points);
    }

    /**
     * @return list<LoyaltyEvent>
     */
    public function pendingEvents(): array
    {
        return $this->pendingEvents;
    }

    public function clearPendingEvents(): void
    {
        $this->pendingEvents = [];
    }
}
