<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Tests\Loyalty\Integration;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Accounting\Loyalty\Domain\AccountingPractice;
use SoftwareArchetypes\Accounting\Loyalty\Domain\AccountType;
use SoftwareArchetypes\Accounting\Loyalty\Domain\LoyaltyAccount;
use SoftwareArchetypes\Accounting\Loyalty\Domain\LoyaltyAccountId;
use SoftwareArchetypes\Accounting\Loyalty\Domain\MarketId;
use SoftwareArchetypes\Accounting\Loyalty\Domain\PostingRules\MaturationPeriodExpiredPostingRule;
use SoftwareArchetypes\Accounting\Loyalty\Domain\PostingRules\PointsRedeemedPostingRule;
use SoftwareArchetypes\Accounting\Loyalty\Domain\PostingRules\PromotionAwardedPostingRule;
use SoftwareArchetypes\Accounting\Loyalty\Domain\PostingRules\PurchaseCompletedPostingRule;
use SoftwareArchetypes\Accounting\Loyalty\Domain\PostingRules\ReturnAcceptedPostingRule;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transactions\MaturationPeriodExpired;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transactions\PointsRedeemed;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transactions\PromotionAwarded;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transactions\PurchaseCompleted;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transactions\ReturnAccepted;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Points;
use SoftwareArchetypes\Accounting\Money;

/**
 * Chicago School TDD: End-to-end integration tests for complete loyalty program workflows.
 *
 * Tests real business scenarios with all components working together:
 * - Purchases, returns, promotions, maturation, redemption
 * - Multi-market operations
 * - Line-level allocation
 * - Entry-based ledger audit trail
 */
final class LoyaltyAccountWorkflowTest extends TestCase
{
    public function testCompleteCustomerJourney(): void
    {
        // Setup Polish market practice
        $practice = AccountingPractice::forMarket(
            MarketId::fromString('PL'),
            'Poland',
            10, // 10 points per PLN
            14, // 14 days maturation
            365,
            true,
            ['JACKET-001' => 2.0] // 2x for jackets
        );

        // Create account and register rules
        $account = LoyaltyAccount::create(
            LoyaltyAccountId::generate(),
            'CUST-001',
            'Jan Kowalski',
            $practice
        );
        $this->registerAllRules($account);

        // Day 1: Customer makes purchase (2 items)
        $purchase = new PurchaseCompleted(
            'TXN-001',
            'PURCH-001',
            'CUST-001',
            Money::of(15000), // 150 PLN
            [
                'item1' => ['lineItemId' => 'LINE-001', 'amount' => Money::of(5000), 'productId' => 'SHIRT-001'], // 500 points
                'item2' => ['lineItemId' => 'LINE-002', 'amount' => Money::of(10000), 'productId' => 'JACKET-001'], // 1000 × 2.0 = 2000 points
            ],
            MarketId::fromString('PL'),
            new DateTimeImmutable('2024-01-01')
        );
        $account->processTransaction($purchase);

        self::assertSame(0, $account->activePoints()->amount());
        self::assertSame(2500, $account->totalPendingPoints()->amount());

        // Day 2: Check-in bonus (immediate)
        $checkIn = new PromotionAwarded(
            'TXN-002',
            'CUST-001',
            'CHECKIN-BONUS',
            'daily-checkin',
            Points::of(100),
            true, // immediate
            null,
            new DateTimeImmutable('2024-01-02')
        );
        $account->processTransaction($checkIn);

        self::assertSame(100, $account->activePoints()->amount());
        self::assertSame(2500, $account->totalPendingPoints()->amount());

        // Day 5: Customer returns jacket (before maturation)
        $return = new ReturnAccepted(
            'TXN-003',
            'PURCH-001',
            'CUST-001',
            ['LINE-002'], // Return only jacket
            new DateTimeImmutable('2024-01-05')
        );
        $account->processTransaction($return);

        self::assertSame(100, $account->activePoints()->amount());
        self::assertSame(500, $account->totalPendingPoints()->amount()); // Only shirt remains
        self::assertSame(2000, $account->reversedPoints()->amount()); // Jacket reversed

        // Day 16: Maturation period expired - activate remaining pending points
        $pendingEntries = $account->account(AccountType::PENDING_FROM_PURCHASES)->entries();
        $maturation = new MaturationPeriodExpired(
            'TXN-004',
            'CUST-001',
            AccountType::PENDING_FROM_PURCHASES,
            array_map(fn($e) => $e->id()->toString(), $pendingEntries),
            new DateTimeImmutable('2024-01-16')
        );
        $account->processTransaction($maturation);

        self::assertSame(600, $account->activePoints()->amount()); // 100 (promo) + 500 (shirt)
        self::assertSame(0, $account->totalPendingPoints()->amount());

        // Day 20: Customer redeems 200 points
        $redemption = new PointsRedeemed(
            'TXN-005',
            'CUST-001',
            Points::of(200),
            'REDEMPTION-001',
            'voucher',
            new DateTimeImmutable('2024-01-20')
        );
        $account->processTransaction($redemption);

        self::assertSame(400, $account->activePoints()->amount());
        self::assertSame(200, $account->spentPoints()->amount());

        // Final state verification
        self::assertSame(400, $account->balance(AccountType::ACTIVE_POINTS)->amount());
        self::assertSame(200, $account->balance(AccountType::SPENT_POINTS)->amount());
        self::assertSame(2000, $account->balance(AccountType::REVERSED_POINTS)->amount());

        // Audit trail - all transactions recorded
        self::assertCount(5, $account->transactions());

        // Complete ledger - all entries traceable
        $allEntries = $account->allEntries();
        self::assertGreaterThan(5, count($allEntries)); // Multiple entries per transaction
    }

    public function testMultiMarketOperations(): void
    {
        // Polish market
        $practicePL = AccountingPractice::forMarket(
            MarketId::fromString('PL'),
            'Poland',
            10, // 10 points per PLN
            14
        );

        $accountPL = LoyaltyAccount::create(
            LoyaltyAccountId::generate(),
            'CUST-001',
            'Test',
            $practicePL
        );
        $this->registerAllRules($accountPL);

        // German market
        $practiceDE = AccountingPractice::forMarket(
            MarketId::fromString('DE'),
            'Germany',
            15, // 15 points per EUR
            30 // Different maturation period
        );

        $accountDE = LoyaltyAccount::create(
            LoyaltyAccountId::generate(),
            'CUST-001',
            'Test',
            $practiceDE
        );
        $this->registerAllRules($accountDE);

        // Same monetary value, different points
        $amount = Money::of(10000); // 100 currency units

        $purchasePL = new PurchaseCompleted(
            'TXN-PL',
            'PURCH-PL',
            'CUST-001',
            $amount,
            ['item1' => ['lineItemId' => 'L1', 'amount' => $amount, 'productId' => 'P1']],
            MarketId::fromString('PL'),
            new DateTimeImmutable('2024-01-01')
        );
        $accountPL->processTransaction($purchasePL);

        $purchaseDE = new PurchaseCompleted(
            'TXN-DE',
            'PURCH-DE',
            'CUST-001',
            $amount,
            ['item1' => ['lineItemId' => 'L1', 'amount' => $amount, 'productId' => 'P1']],
            MarketId::fromString('DE'),
            new DateTimeImmutable('2024-01-01')
        );
        $accountDE->processTransaction($purchaseDE);

        // Poland: 100 PLN = 1000 points
        self::assertSame(1000, $accountPL->totalPendingPoints()->amount());

        // Germany: 100 EUR = 1500 points
        self::assertSame(1500, $accountDE->totalPendingPoints()->amount());
    }

    public function testImmutableAuditTrail(): void
    {
        $practice = AccountingPractice::forMarket(
            MarketId::fromString('PL'),
            'Poland',
            10,
            14
        );

        $account = LoyaltyAccount::create(
            LoyaltyAccountId::generate(),
            'CUST-001',
            'Test',
            $practice
        );
        $this->registerAllRules($account);

        // Multiple operations
        $purchase = new PurchaseCompleted(
            'TXN-001',
            'PURCH-001',
            'CUST-001',
            Money::of(10000),
            ['item1' => ['lineItemId' => 'L1', 'amount' => Money::of(10000), 'productId' => 'P1']],
            MarketId::fromString('PL'),
            new DateTimeImmutable('2024-01-01')
        );
        $account->processTransaction($purchase);

        $promo = new PromotionAwarded(
            'TXN-002',
            'CUST-001',
            'PROMO',
            'bonus',
            Points::of(100),
            true,
            null,
            new DateTimeImmutable('2024-01-02')
        );
        $account->processTransaction($promo);

        $return = new ReturnAccepted(
            'TXN-003',
            'PURCH-001',
            'CUST-001',
            ['L1'],
            new DateTimeImmutable('2024-01-03')
        );
        $account->processTransaction($return);

        // Get all entries from all accounts
        $allEntries = $account->allEntries();

        // Each entry is immutable - verify we can't modify
        foreach ($allEntries as $entry) {
            self::assertInstanceOf(\DateTimeImmutable::class, $entry->effectiveDate());
            // Entry properties are readonly - can't be changed after creation
        }

        // Complete history preserved
        self::assertGreaterThan(0, count($allEntries));

        // Can rebuild state at any point by summing entries
        $calculatedActiveBalance = $this->sumEntriesForAccount(
            $account->account(AccountType::ACTIVE_POINTS)->entries()
        );
        self::assertEquals(
            $account->activePoints()->amount(),
            $calculatedActiveBalance
        );
    }

    private function registerAllRules(LoyaltyAccount $account): void
    {
        $account->registerPostingRule(new PurchaseCompletedPostingRule());
        $account->registerPostingRule(new ReturnAcceptedPostingRule());
        $account->registerPostingRule(new PromotionAwardedPostingRule());
        $account->registerPostingRule(new MaturationPeriodExpiredPostingRule());
        $account->registerPostingRule(new PointsRedeemedPostingRule());
    }

    /**
     * @param list<\SoftwareArchetypes\Accounting\Loyalty\Domain\Entry> $entries
     */
    private function sumEntriesForAccount(array $entries): int
    {
        return array_reduce(
            $entries,
            fn(int $sum, $entry) => $sum + $entry->amount()->amount(),
            0
        );
    }
}
