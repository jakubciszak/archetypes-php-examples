<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Tests\Loyalty\Domain\PostingRules;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Accounting\Loyalty\Domain\AccountingPractice;
use SoftwareArchetypes\Accounting\Loyalty\Domain\AccountType;
use SoftwareArchetypes\Accounting\Loyalty\Domain\LoyaltyAccount;
use SoftwareArchetypes\Accounting\Loyalty\Domain\LoyaltyAccountId;
use SoftwareArchetypes\Accounting\Loyalty\Domain\MarketId;
use SoftwareArchetypes\Accounting\Loyalty\Domain\PostingRules\MaturationPeriodExpiredPostingRule;
use SoftwareArchetypes\Accounting\Loyalty\Domain\PostingRules\PurchaseCompletedPostingRule;
use SoftwareArchetypes\Accounting\Loyalty\Domain\PostingRules\ReturnAcceptedPostingRule;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transactions\MaturationPeriodExpired;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transactions\PurchaseCompleted;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transactions\ReturnAccepted;
use SoftwareArchetypes\Accounting\Money;

/**
 * Chicago School TDD: Test ReturnAcceptedPostingRule with full workflow.
 * Focus on: pending reversal, active reversal, line-level allocation, partial returns.
 */
final class ReturnAcceptedPostingRuleTest extends TestCase
{
    private AccountingPractice $practice;
    private LoyaltyAccount $account;

    protected function setUp(): void
    {
        $this->practice = AccountingPractice::forMarket(
            MarketId::fromString('PL'),
            'Poland',
            10,
            14
        );

        $this->account = LoyaltyAccount::create(
            LoyaltyAccountId::generate(),
            'CUST-001',
            'Test',
            $this->practice
        );

        $this->account->registerPostingRule(new PurchaseCompletedPostingRule());
        $this->account->registerPostingRule(new ReturnAcceptedPostingRule());
        $this->account->registerPostingRule(new MaturationPeriodExpiredPostingRule());
    }

    public function testReversesPendingPointsBeforeActivation(): void
    {
        // Purchase
        $purchase = new PurchaseCompleted(
            'TXN-001',
            'PURCH-001',
            'CUST-001',
            Money::of(10000), // 1000 points
            [
                'item1' => ['lineItemId' => 'LINE-001', 'amount' => Money::of(10000), 'productId' => 'P1'],
            ],
            MarketId::fromString('PL'),
            new DateTimeImmutable('2024-01-01')
        );
        $this->account->processTransaction($purchase);

        self::assertSame(1000, $this->account->totalPendingPoints()->amount());

        // Return before activation
        $return = new ReturnAccepted(
            'TXN-002',
            'PURCH-001',
            'CUST-001',
            ['LINE-001'],
            new DateTimeImmutable('2024-01-05')
        );
        $this->account->processTransaction($return);

        // Pending points reversed
        self::assertTrue($this->account->totalPendingPoints()->isZero());
        self::assertTrue($this->account->activePoints()->isZero());

        // Tracked in ReversedPoints
        self::assertSame(1000, $this->account->reversedPoints()->amount());
    }

    public function testReversesActivePointsAfterActivation(): void
    {
        // Purchase
        $purchase = new PurchaseCompleted(
            'TXN-001',
            'PURCH-001',
            'CUST-001',
            Money::of(10000), // 1000 points
            [
                'item1' => ['lineItemId' => 'LINE-001', 'amount' => Money::of(10000), 'productId' => 'P1'],
            ],
            MarketId::fromString('PL'),
            new DateTimeImmutable('2024-01-01')
        );
        $this->account->processTransaction($purchase);

        // Activate points
        $pendingEntries = $this->account->account(AccountType::PENDING_FROM_PURCHASES)->entries();
        $maturation = new MaturationPeriodExpired(
            'TXN-MATURE',
            'CUST-001',
            AccountType::PENDING_FROM_PURCHASES,
            array_map(fn($e) => $e->id()->toString(), $pendingEntries),
            new DateTimeImmutable('2024-01-16')
        );
        $this->account->processTransaction($maturation);

        self::assertSame(1000, $this->account->activePoints()->amount());

        // Return after activation
        $return = new ReturnAccepted(
            'TXN-002',
            'PURCH-001',
            'CUST-001',
            ['LINE-001'],
            new DateTimeImmutable('2024-01-20')
        );
        $this->account->processTransaction($return);

        // Active points deducted
        self::assertTrue($this->account->activePoints()->isZero());

        // Tracked in ReversedPoints
        self::assertSame(1000, $this->account->reversedPoints()->amount());
    }

    public function testPartialReturnWithLineLevelAllocation(): void
    {
        // Purchase with 3 items
        $purchase = new PurchaseCompleted(
            'TXN-001',
            'PURCH-001',
            'CUST-001',
            Money::of(30000), // 300 PLN
            [
                'item1' => ['lineItemId' => 'LINE-001', 'amount' => Money::of(10000), 'productId' => 'P1'], // 1000 points
                'item2' => ['lineItemId' => 'LINE-002', 'amount' => Money::of(10000), 'productId' => 'P2'], // 1000 points
                'item3' => ['lineItemId' => 'LINE-003', 'amount' => Money::of(10000), 'productId' => 'P3'], // 1000 points
            ],
            MarketId::fromString('PL'),
            new DateTimeImmutable('2024-01-01')
        );
        $this->account->processTransaction($purchase);

        self::assertSame(3000, $this->account->totalPendingPoints()->amount());

        // Return only LINE-002
        $return = new ReturnAccepted(
            'TXN-002',
            'PURCH-001',
            'CUST-001',
            ['LINE-002'], // Only this one!
            new DateTimeImmutable('2024-01-05')
        );
        $this->account->processTransaction($return);

        // Only 1000 points reversed
        self::assertSame(2000, $this->account->totalPendingPoints()->amount());
        self::assertSame(1000, $this->account->reversedPoints()->amount());

        // LINE-001 and LINE-003 still intact
        self::assertSame(1000, $this->account->balanceForLineItem('LINE-001', AccountType::PENDING_FROM_PURCHASES)->amount());
        self::assertSame(1000, $this->account->balanceForLineItem('LINE-003', AccountType::PENDING_FROM_PURCHASES)->amount());
        self::assertSame(0, $this->account->balanceForLineItem('LINE-002', AccountType::PENDING_FROM_PURCHASES)->amount());
    }

    public function testMultiplePartialReturns(): void
    {
        // Purchase
        $purchase = new PurchaseCompleted(
            'TXN-001',
            'PURCH-001',
            'CUST-001',
            Money::of(30000),
            [
                'item1' => ['lineItemId' => 'LINE-001', 'amount' => Money::of(10000), 'productId' => 'P1'],
                'item2' => ['lineItemId' => 'LINE-002', 'amount' => Money::of(10000), 'productId' => 'P2'],
                'item3' => ['lineItemId' => 'LINE-003', 'amount' => Money::of(10000), 'productId' => 'P3'],
            ],
            MarketId::fromString('PL'),
            new DateTimeImmutable('2024-01-01')
        );
        $this->account->processTransaction($purchase);

        // First return
        $return1 = new ReturnAccepted(
            'TXN-002',
            'PURCH-001',
            'CUST-001',
            ['LINE-001'],
            new DateTimeImmutable('2024-01-05')
        );
        $this->account->processTransaction($return1);

        self::assertSame(2000, $this->account->totalPendingPoints()->amount());

        // Second return
        $return2 = new ReturnAccepted(
            'TXN-003',
            'PURCH-001',
            'CUST-001',
            ['LINE-003'],
            new DateTimeImmutable('2024-01-07')
        );
        $this->account->processTransaction($return2);

        // Only LINE-002 remains
        self::assertSame(1000, $this->account->totalPendingPoints()->amount());
        self::assertSame(2000, $this->account->reversedPoints()->amount());
    }

    public function testCreatesNegativeEntriesForReversals(): void
    {
        // Purchase
        $purchase = new PurchaseCompleted(
            'TXN-001',
            'PURCH-001',
            'CUST-001',
            Money::of(10000),
            [
                'item1' => ['lineItemId' => 'LINE-001', 'amount' => Money::of(10000), 'productId' => 'P1'],
            ],
            MarketId::fromString('PL'),
            new DateTimeImmutable('2024-01-01')
        );
        $this->account->processTransaction($purchase);

        // Return
        $return = new ReturnAccepted(
            'TXN-002',
            'PURCH-001',
            'CUST-001',
            ['LINE-001'],
            new DateTimeImmutable('2024-01-05')
        );
        $this->account->processTransaction($return);

        // Check entries in PendingFromPurchases
        $pendingEntries = $this->account->account(AccountType::PENDING_FROM_PURCHASES)->entries();

        // Should have: +1000 (original), -1000 (reversal)
        self::assertCount(2, $pendingEntries);
        self::assertSame(1000, $pendingEntries[0]->amount()->amount());   // Original
        self::assertSame(-1000, $pendingEntries[1]->amount()->amount());  // Reversal (negative!)

        // Balance = 1000 - 1000 = 0
        self::assertTrue($this->account->balance(AccountType::PENDING_FROM_PURCHASES)->isZero());
    }
}
