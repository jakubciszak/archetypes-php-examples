<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Tests\Loyalty\Domain;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Accounting\Loyalty\Domain\CheckInSeriesAction;
use SoftwareArchetypes\Accounting\Loyalty\Domain\LoyaltyAccount;
use SoftwareArchetypes\Accounting\Loyalty\Domain\LoyaltyAccountId;
use SoftwareArchetypes\Accounting\Loyalty\Domain\MarketId;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Points;
use SoftwareArchetypes\Accounting\Loyalty\Domain\PostingRule;
use SoftwareArchetypes\Accounting\Loyalty\Domain\ProductBonusAction;
use SoftwareArchetypes\Accounting\Loyalty\Domain\PurchaseId;
use SoftwareArchetypes\Accounting\Loyalty\Domain\QuickPickupAction;
use SoftwareArchetypes\Accounting\Loyalty\Events\PointsActivated;
use SoftwareArchetypes\Accounting\Loyalty\Events\PointsEarned;
use SoftwareArchetypes\Accounting\Loyalty\Events\PointsReversed;
use SoftwareArchetypes\Accounting\Loyalty\Events\PromotionalPointsAwarded;
use SoftwareArchetypes\Accounting\Money;

final class LoyaltyAccountTest extends TestCase
{
    public function testCreatesLoyaltyAccount(): void
    {
        $accountId = LoyaltyAccountId::generate();
        $account = LoyaltyAccount::create(
            $accountId,
            'CUST-001',
            'Jan Kowalski',
        );

        self::assertTrue($account->id()->equals($accountId));
        self::assertSame('CUST-001', $account->customerId());
        self::assertSame('Jan Kowalski', $account->customerName());
        self::assertTrue($account->activePoints()->isZero());
        self::assertTrue($account->totalPendingPoints()->isZero());
    }

    public function testRecordsPurchaseAndCreatesPendingPoints(): void
    {
        $account = LoyaltyAccount::create(
            LoyaltyAccountId::generate(),
            'CUST-001',
            'Jan Kowalski',
        );

        $purchaseId = PurchaseId::generate();
        $postingRule = PostingRule::create(
            MarketId::fromString('PL'),
            'Poland',
            10, // 10 points per PLN
            14, // 14 days return period
        );

        $purchaseDate = new DateTimeImmutable('2024-01-01');
        $account->recordPurchase(
            $purchaseId,
            Money::of(10000), // 100 PLN = 1000 points
            $postingRule,
            $purchaseDate,
        );

        // Points are pending, not active yet
        self::assertTrue($account->activePoints()->isZero());
        self::assertSame(1000, $account->totalPendingPoints()->amount());

        // Verify event published
        $events = $account->pendingEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(PointsEarned::class, $events[0]);

        /** @var PointsEarned $event */
        $event = $events[0];
        self::assertTrue($event->purchaseId()->equals($purchaseId));
        self::assertSame(1000, $event->points()->amount());
        self::assertSame('2024-01-15', $event->activationDate()->format('Y-m-d'));
    }

    public function testRecordsPurchaseWithZeroPointsDoesNotCreatePending(): void
    {
        $account = LoyaltyAccount::create(
            LoyaltyAccountId::generate(),
            'CUST-001',
            'Jan Kowalski',
        );

        $postingRule = PostingRule::create(
            MarketId::fromString('PL'),
            'Poland',
            10,
            14,
        );

        // Very small purchase that rounds to 0 points
        $account->recordPurchase(
            PurchaseId::generate(),
            Money::of(1), // 0.01 PLN = 0 points
            $postingRule,
            new DateTimeImmutable('2024-01-01'),
        );

        self::assertTrue($account->totalPendingPoints()->isZero());
        self::assertCount(0, $account->pendingEvents());
    }

    public function testActivatesPendingPoints(): void
    {
        $account = LoyaltyAccount::create(
            LoyaltyAccountId::generate(),
            'CUST-001',
            'Jan Kowalski',
        );

        $purchaseId = PurchaseId::generate();
        $postingRule = PostingRule::create(
            MarketId::fromString('PL'),
            'Poland',
            10,
            14, // 14 days
        );

        $purchaseDate = new DateTimeImmutable('2024-01-01');
        $account->recordPurchase(
            $purchaseId,
            Money::of(10000), // 1000 points
            $postingRule,
            $purchaseDate,
        );

        $account->clearPendingEvents();

        // Activate after 14 days
        $activationDate = new DateTimeImmutable('2024-01-15');
        $account->activatePendingPoints($activationDate);

        self::assertSame(1000, $account->activePoints()->amount());
        self::assertSame(0, $account->totalPendingPoints()->amount());

        // Verify event
        $events = $account->pendingEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(PointsActivated::class, $events[0]);
    }

    public function testDoesNotActivatePointsBeforeActivationDate(): void
    {
        $account = LoyaltyAccount::create(
            LoyaltyAccountId::generate(),
            'CUST-001',
            'Jan Kowalski',
        );

        $postingRule = PostingRule::create(
            MarketId::fromString('PL'),
            'Poland',
            10,
            14,
        );

        $purchaseDate = new DateTimeImmutable('2024-01-01');
        $account->recordPurchase(
            PurchaseId::generate(),
            Money::of(10000),
            $postingRule,
            $purchaseDate,
        );

        $account->clearPendingEvents();

        // Try to activate before 14 days
        $earlyDate = new DateTimeImmutable('2024-01-10');
        $account->activatePendingPoints($earlyDate);

        // Points still pending
        self::assertTrue($account->activePoints()->isZero());
        self::assertSame(1000, $account->totalPendingPoints()->amount());
        self::assertCount(0, $account->pendingEvents());
    }

    public function testAwardsPromotionalPoints(): void
    {
        $account = LoyaltyAccount::create(
            LoyaltyAccountId::generate(),
            'CUST-001',
            'Jan Kowalski',
        );

        $action = CheckInSeriesAction::create(
            'checkin-7days',
            7,
            Points::of(100),
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-12-31'),
        );

        $awardDate = new DateTimeImmutable('2024-01-10');
        $account->awardPromotionalPoints($action, $awardDate);

        // Promotional points are immediately active
        self::assertSame(100, $account->activePoints()->amount());

        // Verify event
        $events = $account->pendingEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(PromotionalPointsAwarded::class, $events[0]);

        /** @var PromotionalPointsAwarded $event */
        $event = $events[0];
        self::assertSame('checkin-7days', $event->actionId());
        self::assertSame(100, $event->points()->amount());
    }

    public function testCannotAwardPromotionalPointsOutsideValidPeriod(): void
    {
        $account = LoyaltyAccount::create(
            LoyaltyAccountId::generate(),
            'CUST-001',
            'Jan Kowalski',
        );

        $action = CheckInSeriesAction::create(
            'checkin-7days',
            7,
            Points::of(100),
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-01-31'),
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Promotional action is not applicable on this date');

        // Outside valid period
        $account->awardPromotionalPoints($action, new DateTimeImmutable('2024-02-01'));
    }

    public function testAwardsProductBonusPoints(): void
    {
        $account = LoyaltyAccount::create(
            LoyaltyAccountId::generate(),
            'CUST-001',
            'Jan Kowalski',
        );

        $action = ProductBonusAction::create(
            'product-bonus-001',
            'PROD-123',
            'Premium Jacket',
            Points::of(50),
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-12-31'),
        );

        $account->awardPromotionalPoints($action, new DateTimeImmutable('2024-01-15'));

        self::assertSame(50, $account->activePoints()->amount());
    }

    public function testAwardsQuickPickupPoints(): void
    {
        $account = LoyaltyAccount::create(
            LoyaltyAccountId::generate(),
            'CUST-001',
            'Jan Kowalski',
        );

        $action = QuickPickupAction::create(
            'quick-pickup-24h',
            24, // 24 hours
            Points::of(30),
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-12-31'),
        );

        $account->awardPromotionalPoints($action, new DateTimeImmutable('2024-01-15'));

        self::assertSame(30, $account->activePoints()->amount());
    }

    public function testReversesPendingPurchase(): void
    {
        $account = LoyaltyAccount::create(
            LoyaltyAccountId::generate(),
            'CUST-001',
            'Jan Kowalski',
        );

        $purchaseId = PurchaseId::generate();
        $postingRule = PostingRule::create(
            MarketId::fromString('PL'),
            'Poland',
            10,
            14,
        );

        $account->recordPurchase(
            $purchaseId,
            Money::of(10000), // 1000 points
            $postingRule,
            new DateTimeImmutable('2024-01-01'),
        );

        $account->clearPendingEvents();

        // Return before activation
        $account->reversePurchase($purchaseId, new DateTimeImmutable('2024-01-05'));

        // Points marked as reversed
        self::assertTrue($account->activePoints()->isZero());

        // Verify event
        $events = $account->pendingEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(PointsReversed::class, $events[0]);
    }

    public function testReversesActivatedPurchase(): void
    {
        $account = LoyaltyAccount::create(
            LoyaltyAccountId::generate(),
            'CUST-001',
            'Jan Kowalski',
        );

        $purchaseId = PurchaseId::generate();
        $postingRule = PostingRule::create(
            MarketId::fromString('PL'),
            'Poland',
            10,
            14,
        );

        $purchaseDate = new DateTimeImmutable('2024-01-01');
        $account->recordPurchase(
            $purchaseId,
            Money::of(10000), // 1000 points
            $postingRule,
            $purchaseDate,
        );

        // Activate points
        $account->activatePendingPoints(new DateTimeImmutable('2024-01-15'));
        self::assertSame(1000, $account->activePoints()->amount());

        $account->clearPendingEvents();

        // Return after activation
        $account->reversePurchase($purchaseId, new DateTimeImmutable('2024-01-20'));

        // Points deducted from active
        self::assertTrue($account->activePoints()->isZero());

        $events = $account->pendingEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(PointsReversed::class, $events[0]);
    }

    public function testCannotReversePurchaseThatDoesNotExist(): void
    {
        $account = LoyaltyAccount::create(
            LoyaltyAccountId::generate(),
            'CUST-001',
            'Jan Kowalski',
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Purchase');
        $this->expectExceptionMessage('not found');

        $account->reversePurchase(
            PurchaseId::generate(),
            new DateTimeImmutable('2024-01-01'),
        );
    }

    public function testUsesPoints(): void
    {
        $account = LoyaltyAccount::create(
            LoyaltyAccountId::generate(),
            'CUST-001',
            'Jan Kowalski',
        );

        // Award promotional points (immediately active)
        $action = CheckInSeriesAction::create(
            'checkin',
            7,
            Points::of(1000),
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-12-31'),
        );
        $account->awardPromotionalPoints($action, new DateTimeImmutable('2024-01-15'));

        // Use some points
        $account->usePoints(Points::of(300));

        self::assertSame(700, $account->activePoints()->amount());
    }

    public function testCannotUseMorePointsThanAvailable(): void
    {
        $account = LoyaltyAccount::create(
            LoyaltyAccountId::generate(),
            'CUST-001',
            'Jan Kowalski',
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Insufficient points');

        $account->usePoints(Points::of(100));
    }

    public function testComplexScenarioWithMultiplePurchasesAndPromotions(): void
    {
        $account = LoyaltyAccount::create(
            LoyaltyAccountId::generate(),
            'CUST-001',
            'Jan Kowalski',
        );

        $postingRule = PostingRule::create(
            MarketId::fromString('PL'),
            'Poland',
            10,
            14,
        );

        // Purchase 1
        $purchase1 = PurchaseId::generate();
        $account->recordPurchase(
            $purchase1,
            Money::of(10000), // 1000 points
            $postingRule,
            new DateTimeImmutable('2024-01-01'),
        );

        // Purchase 2
        $purchase2 = PurchaseId::generate();
        $account->recordPurchase(
            $purchase2,
            Money::of(5000), // 500 points
            $postingRule,
            new DateTimeImmutable('2024-01-05'),
        );

        // Both pending
        self::assertSame(1500, $account->totalPendingPoints()->amount());
        self::assertTrue($account->activePoints()->isZero());

        // Award promotional points
        $action = CheckInSeriesAction::create(
            'checkin',
            7,
            Points::of(200),
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-12-31'),
        );
        $account->awardPromotionalPoints($action, new DateTimeImmutable('2024-01-10'));

        self::assertSame(200, $account->activePoints()->amount());

        // Activate first purchase
        $account->activatePendingPoints(new DateTimeImmutable('2024-01-15'));
        self::assertSame(1200, $account->activePoints()->amount()); // 200 + 1000

        // Return second purchase before activation
        $account->reversePurchase($purchase2, new DateTimeImmutable('2024-01-16'));

        // Still have 1200 active (first purchase + promo)
        self::assertSame(1200, $account->activePoints()->amount());

        // Use some points
        $account->usePoints(Points::of(500));
        self::assertSame(700, $account->activePoints()->amount());
    }
}
