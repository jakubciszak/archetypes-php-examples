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
use SoftwareArchetypes\Accounting\Loyalty\Domain\PostingRules\PurchaseCompletedPostingRule;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Transactions\PurchaseCompleted;
use SoftwareArchetypes\Accounting\Money;

/**
 * Chicago School TDD: Test PurchaseCompletedPostingRule with real objects.
 * Focus on end-to-end behavior: Transaction → Posting Rule → Entries → Balance.
 */
final class PurchaseCompletedPostingRuleTest extends TestCase
{
    private AccountingPractice $practice;
    private LoyaltyAccount $account;

    protected function setUp(): void
    {
        $this->practice = AccountingPractice::forMarket(
            MarketId::fromString('PL'),
            'Poland',
            10, // 10 points per PLN
            14  // 14 days maturation
        );

        $this->account = LoyaltyAccount::create(
            LoyaltyAccountId::generate(),
            'CUST-001',
            'Test Customer',
            $this->practice
        );

        $this->account->registerPostingRule(new PurchaseCompletedPostingRule());
    }

    public function testProcessesPurchaseAndCreatesEntriesInPendingAccount(): void
    {
        $purchase = new PurchaseCompleted(
            'TXN-001',
            'PURCH-001',
            'CUST-001',
            Money::of(10000), // 100 PLN
            [
                'item1' => [
                    'lineItemId' => 'LINE-001',
                    'amount' => Money::of(10000),
                    'productId' => 'PRODUCT-001',
                ],
            ],
            MarketId::fromString('PL'),
            new DateTimeImmutable('2024-01-01')
        );

        $this->account->processTransaction($purchase);

        // Points should be in PendingFromPurchases, not active
        self::assertTrue($this->account->activePoints()->isZero());
        self::assertSame(1000, $this->account->totalPendingPoints()->amount());
        self::assertSame(1000, $this->account->balance(AccountType::PENDING_FROM_PURCHASES)->amount());
    }

    public function testCreatesOneEntryPerLineItem(): void
    {
        $purchase = new PurchaseCompleted(
            'TXN-001',
            'PURCH-001',
            'CUST-001',
            Money::of(15000), // 150 PLN
            [
                'item1' => [
                    'lineItemId' => 'LINE-001',
                    'amount' => Money::of(5000), // 50 PLN = 500 points
                    'productId' => 'PRODUCT-001',
                ],
                'item2' => [
                    'lineItemId' => 'LINE-002',
                    'amount' => Money::of(10000), // 100 PLN = 1000 points
                    'productId' => 'PRODUCT-002',
                ],
            ],
            MarketId::fromString('PL'),
            new DateTimeImmutable('2024-01-01')
        );

        $this->account->processTransaction($purchase);

        $pendingAccount = $this->account->account(AccountType::PENDING_FROM_PURCHASES);
        $entries = $pendingAccount->entries();

        // 2 line items = 2 entries
        self::assertCount(2, $entries);
        self::assertSame(500, $entries[0]->amount()->amount());
        self::assertSame(1000, $entries[1]->amount()->amount());

        // Total balance
        self::assertSame(1500, $pendingAccount->balance()->amount());
    }

    public function testEntriesContainMaturationDateInMetadata(): void
    {
        $purchaseDate = new DateTimeImmutable('2024-01-01');

        $purchase = new PurchaseCompleted(
            'TXN-001',
            'PURCH-001',
            'CUST-001',
            Money::of(5000),
            [
                'item1' => [
                    'lineItemId' => 'LINE-001',
                    'amount' => Money::of(5000),
                    'productId' => 'PRODUCT-001',
                ],
            ],
            MarketId::fromString('PL'),
            $purchaseDate
        );

        $this->account->processTransaction($purchase);

        $entries = $this->account->account(AccountType::PENDING_FROM_PURCHASES)->entries();
        $metadata = $entries[0]->metadata();

        // Maturation date should be purchase date + 14 days
        self::assertArrayHasKey('maturation_date', $metadata);
        self::assertSame('2024-01-15 00:00:00', $metadata['maturation_date']);
    }

    public function testAppliesPromotionalMultiplierFromPractice(): void
    {
        $practiceWithBonus = AccountingPractice::forMarket(
            MarketId::fromString('PL'),
            'Poland',
            10,
            14,
            365,
            true,
            ['JACKET-001' => 2.0] // 2x for jackets
        );

        $accountWithBonus = LoyaltyAccount::create(
            LoyaltyAccountId::generate(),
            'CUST-001',
            'Test',
            $practiceWithBonus
        );
        $accountWithBonus->registerPostingRule(new PurchaseCompletedPostingRule());

        $purchase = new PurchaseCompleted(
            'TXN-001',
            'PURCH-001',
            'CUST-001',
            Money::of(10000), // 100 PLN
            [
                'item1' => [
                    'lineItemId' => 'LINE-001',
                    'amount' => Money::of(10000),
                    'productId' => 'JACKET-001', // Has 2x multiplier!
                ],
            ],
            MarketId::fromString('PL'),
            new DateTimeImmutable('2024-01-01')
        );

        $accountWithBonus->processTransaction($purchase);

        // 100 PLN × 10 points × 2.0 multiplier = 2000 points
        self::assertSame(2000, $accountWithBonus->totalPendingPoints()->amount());
    }

    public function testSkipsLineItemsWithZeroPoints(): void
    {
        $purchase = new PurchaseCompleted(
            'TXN-001',
            'PURCH-001',
            'CUST-001',
            Money::of(1), // Very small amount
            [
                'item1' => [
                    'lineItemId' => 'LINE-001',
                    'amount' => Money::of(1), // 0.01 PLN = 0 points
                    'productId' => 'PRODUCT-001',
                ],
            ],
            MarketId::fromString('PL'),
            new DateTimeImmutable('2024-01-01')
        );

        $this->account->processTransaction($purchase);

        // No entries should be created for zero points
        $entries = $this->account->account(AccountType::PENDING_FROM_PURCHASES)->entries();
        self::assertEmpty($entries);
        self::assertTrue($this->account->totalPendingPoints()->isZero());
    }

    public function testEntriesTrackPurchaseAndLineItemReferences(): void
    {
        $purchase = new PurchaseCompleted(
            'TXN-001',
            'PURCH-001',
            'CUST-001',
            Money::of(5000),
            [
                'item1' => [
                    'lineItemId' => 'LINE-001',
                    'amount' => Money::of(5000),
                    'productId' => 'PRODUCT-001',
                ],
            ],
            MarketId::fromString('PL'),
            new DateTimeImmutable('2024-01-01')
        );

        $this->account->processTransaction($purchase);

        $entries = $this->account->entriesForReference('PURCH-001');
        self::assertCount(1, $entries);
        self::assertSame('PURCH-001', $entries[0]->referenceId());
        self::assertSame('LINE-001', $entries[0]->lineItemId());
    }
}
