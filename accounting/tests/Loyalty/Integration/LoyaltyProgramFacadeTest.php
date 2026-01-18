<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Tests\Loyalty\Integration;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Accounting\Loyalty\Domain\CheckInSeriesAction;
use SoftwareArchetypes\Accounting\Loyalty\Domain\LoyaltyAccountId;
use SoftwareArchetypes\Accounting\Loyalty\Domain\MarketId;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Points;
use SoftwareArchetypes\Accounting\Loyalty\Domain\PostingRule;
use SoftwareArchetypes\Accounting\Loyalty\Domain\ProductBonusAction;
use SoftwareArchetypes\Accounting\Loyalty\Domain\PurchaseId;
use SoftwareArchetypes\Accounting\Loyalty\Events\PointsActivated;
use SoftwareArchetypes\Accounting\Loyalty\Events\PointsEarned;
use SoftwareArchetypes\Accounting\Loyalty\Events\PointsReversed;
use SoftwareArchetypes\Accounting\Loyalty\Events\PromotionalPointsAwarded;
use SoftwareArchetypes\Accounting\Loyalty\Infrastructure\InMemoryEventsPublisher;
use SoftwareArchetypes\Accounting\Loyalty\Infrastructure\InMemoryLoyaltyAccountRepository;
use SoftwareArchetypes\Accounting\Loyalty\LoyaltyProgramFacade;
use SoftwareArchetypes\Accounting\Money;

final class LoyaltyProgramFacadeTest extends TestCase
{
    private LoyaltyProgramFacade $facade;
    private InMemoryEventsPublisher $eventsPublisher;

    protected function setUp(): void
    {
        $repository = new InMemoryLoyaltyAccountRepository();
        $this->eventsPublisher = new InMemoryEventsPublisher();
        $this->facade = new LoyaltyProgramFacade($repository, $this->eventsPublisher);
    }

    public function testCreatesLoyaltyAccount(): void
    {
        $accountId = LoyaltyAccountId::generate();

        $account = $this->facade->createAccount(
            $accountId,
            'CUST-001',
            'Jan Kowalski',
        );

        self::assertNotNull($account);
        self::assertTrue($account->id()->equals($accountId));
        self::assertSame('CUST-001', $account->customerId());
    }

    public function testFindsAccountByCustomerId(): void
    {
        $accountId = LoyaltyAccountId::generate();
        $this->facade->createAccount($accountId, 'CUST-001', 'Jan Kowalski');

        $found = $this->facade->findAccountByCustomerId('CUST-001');

        self::assertNotNull($found);
        self::assertTrue($found->id()->equals($accountId));
    }

    public function testRecordsPurchaseAndPublishesEvent(): void
    {
        $accountId = LoyaltyAccountId::generate();
        $this->facade->createAccount($accountId, 'CUST-001', 'Jan Kowalski');

        $purchaseId = PurchaseId::generate();
        $postingRule = PostingRule::create(
            MarketId::fromString('PL'),
            'Poland',
            10,
            14,
        );

        $this->facade->recordPurchase(
            $accountId,
            $purchaseId,
            Money::of(10000), // 1000 points
            $postingRule,
            new DateTimeImmutable('2024-01-01'),
        );

        // Verify points are pending
        $pendingPoints = $this->facade->getPendingPoints($accountId);
        self::assertNotNull($pendingPoints);
        self::assertSame(1000, $pendingPoints->amount());

        // Verify event published
        $events = $this->eventsPublisher->publishedEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(PointsEarned::class, $events[0]);
    }

    public function testAwardsPromotionalPointsAndPublishesEvent(): void
    {
        $accountId = LoyaltyAccountId::generate();
        $this->facade->createAccount($accountId, 'CUST-001', 'Jan Kowalski');

        $action = CheckInSeriesAction::create(
            'checkin-7days',
            7,
            Points::of(100),
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-12-31'),
        );

        $this->facade->awardPromotionalPoints(
            $accountId,
            $action,
            new DateTimeImmutable('2024-01-10'),
        );

        // Verify points are immediately active
        $activePoints = $this->facade->getActivePoints($accountId);
        self::assertNotNull($activePoints);
        self::assertSame(100, $activePoints->amount());

        // Verify event published
        $events = $this->eventsPublisher->publishedEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(PromotionalPointsAwarded::class, $events[0]);
    }

    public function testActivatesPendingPointsAndPublishesEvent(): void
    {
        $accountId = LoyaltyAccountId::generate();
        $this->facade->createAccount($accountId, 'CUST-001', 'Jan Kowalski');

        $postingRule = PostingRule::create(
            MarketId::fromString('PL'),
            'Poland',
            10,
            14,
        );

        $this->facade->recordPurchase(
            $accountId,
            PurchaseId::generate(),
            Money::of(10000),
            $postingRule,
            new DateTimeImmutable('2024-01-01'),
        );

        $this->eventsPublisher->clear();

        // Activate after 14 days
        $this->facade->activatePendingPoints(
            $accountId,
            new DateTimeImmutable('2024-01-15'),
        );

        // Verify points are now active
        $activePoints = $this->facade->getActivePoints($accountId);
        self::assertNotNull($activePoints);
        self::assertSame(1000, $activePoints->amount());

        $pendingPoints = $this->facade->getPendingPoints($accountId);
        self::assertNotNull($pendingPoints);
        self::assertTrue($pendingPoints->isZero());

        // Verify event published
        $events = $this->eventsPublisher->publishedEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(PointsActivated::class, $events[0]);
    }

    public function testActivatesAllPendingPointsForMultipleAccounts(): void
    {
        // Create multiple accounts with purchases
        $account1 = LoyaltyAccountId::generate();
        $this->facade->createAccount($account1, 'CUST-001', 'Jan Kowalski');

        $account2 = LoyaltyAccountId::generate();
        $this->facade->createAccount($account2, 'CUST-002', 'Anna Nowak');

        $postingRule = PostingRule::create(
            MarketId::fromString('PL'),
            'Poland',
            10,
            14,
        );

        $purchaseDate = new DateTimeImmutable('2024-01-01');

        $this->facade->recordPurchase(
            $account1,
            PurchaseId::generate(),
            Money::of(10000),
            $postingRule,
            $purchaseDate,
        );

        $this->facade->recordPurchase(
            $account2,
            PurchaseId::generate(),
            Money::of(5000),
            $postingRule,
            $purchaseDate,
        );

        $this->eventsPublisher->clear();

        // Activate all pending points
        $this->facade->activateAllPendingPoints(new DateTimeImmutable('2024-01-15'));

        // Verify both accounts have active points
        $points1 = $this->facade->getActivePoints($account1);
        $points2 = $this->facade->getActivePoints($account2);

        self::assertNotNull($points1);
        self::assertNotNull($points2);
        self::assertSame(1000, $points1->amount());
        self::assertSame(500, $points2->amount());

        // Verify events published for both
        $events = $this->eventsPublisher->publishedEvents();
        self::assertCount(2, $events);
    }

    public function testReversesPurchaseAndPublishesEvent(): void
    {
        $accountId = LoyaltyAccountId::generate();
        $this->facade->createAccount($accountId, 'CUST-001', 'Jan Kowalski');

        $purchaseId = PurchaseId::generate();
        $postingRule = PostingRule::create(
            MarketId::fromString('PL'),
            'Poland',
            10,
            14,
        );

        $this->facade->recordPurchase(
            $accountId,
            $purchaseId,
            Money::of(10000),
            $postingRule,
            new DateTimeImmutable('2024-01-01'),
        );

        $this->eventsPublisher->clear();

        // Return the purchase
        $this->facade->reversePurchase(
            $accountId,
            $purchaseId,
            new DateTimeImmutable('2024-01-05'),
        );

        // Verify event published
        $events = $this->eventsPublisher->publishedEvents();
        self::assertCount(1, $events);
        self::assertInstanceOf(PointsReversed::class, $events[0]);
    }

    public function testUsesPoints(): void
    {
        $accountId = LoyaltyAccountId::generate();
        $this->facade->createAccount($accountId, 'CUST-001', 'Jan Kowalski');

        // Award promotional points
        $action = CheckInSeriesAction::create(
            'checkin',
            7,
            Points::of(1000),
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-12-31'),
        );

        $this->facade->awardPromotionalPoints(
            $accountId,
            $action,
            new DateTimeImmutable('2024-01-10'),
        );

        // Use points
        $this->facade->usePoints($accountId, Points::of(300));

        $remaining = $this->facade->getActivePoints($accountId);
        self::assertNotNull($remaining);
        self::assertSame(700, $remaining->amount());
    }

    public function testCompleteEcommerceLoyaltyScenario(): void
    {
        // Create customer account
        $accountId = LoyaltyAccountId::generate();
        $this->facade->createAccount($accountId, 'CUST-LPP-001', 'Maria Wiśniewska');

        // Define posting rules for different markets
        $rulePL = PostingRule::create(
            MarketId::fromString('PL'),
            'Poland',
            10, // 10 points per PLN
            14, // 14 days return period
        );

        $ruleDE = PostingRule::create(
            MarketId::fromString('DE'),
            'Germany',
            15, // 15 points per EUR
            30, // 30 days return period
        );

        // Purchase 1 in Poland
        $purchase1 = PurchaseId::generate();
        $this->facade->recordPurchase(
            $accountId,
            $purchase1,
            Money::of(25000), // 250 PLN = 2500 points
            $rulePL,
            new DateTimeImmutable('2024-01-01'),
        );

        // Purchase 2 in Germany
        $purchase2 = PurchaseId::generate();
        $this->facade->recordPurchase(
            $accountId,
            $purchase2,
            Money::of(10000), // 100 EUR = 1500 points
            $ruleDE,
            new DateTimeImmutable('2024-01-05'),
        );

        // Check-in series bonus
        $checkInAction = CheckInSeriesAction::create(
            'checkin-7days',
            7,
            Points::of(200),
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-12-31'),
        );
        $this->facade->awardPromotionalPoints(
            $accountId,
            $checkInAction,
            new DateTimeImmutable('2024-01-10'),
        );

        // Product bonus
        $productBonus = ProductBonusAction::create(
            'winter-jacket-bonus',
            'PROD-JACKET-001',
            'Winter Jacket',
            Points::of(150),
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-01-31'),
        );
        $this->facade->awardPromotionalPoints(
            $accountId,
            $productBonus,
            new DateTimeImmutable('2024-01-10'),
        );

        // Verify state after bonuses
        $activePoints = $this->facade->getActivePoints($accountId);
        $pendingPoints = $this->facade->getPendingPoints($accountId);

        self::assertNotNull($activePoints);
        self::assertNotNull($pendingPoints);
        self::assertSame(350, $activePoints->amount()); // Promotional points
        self::assertSame(4000, $pendingPoints->amount()); // Purchase points

        // Activate Polish purchase after 14 days
        $this->facade->activatePendingPoints(
            $accountId,
            new DateTimeImmutable('2024-01-15'),
        );

        $activePoints = $this->facade->getActivePoints($accountId);
        self::assertNotNull($activePoints);
        self::assertSame(2850, $activePoints->amount()); // 350 + 2500

        // Activate German purchase after 30 days
        $this->facade->activatePendingPoints(
            $accountId,
            new DateTimeImmutable('2024-02-04'),
        );

        $activePoints = $this->facade->getActivePoints($accountId);
        $pendingPoints = $this->facade->getPendingPoints($accountId);

        self::assertNotNull($activePoints);
        self::assertNotNull($pendingPoints);
        self::assertSame(4350, $activePoints->amount()); // 2850 + 1500
        self::assertTrue($pendingPoints->isZero());

        // Use points for redemption
        $this->facade->usePoints($accountId, Points::of(1000));

        $finalPoints = $this->facade->getActivePoints($accountId);
        self::assertNotNull($finalPoints);
        self::assertSame(3350, $finalPoints->amount());
    }
}
