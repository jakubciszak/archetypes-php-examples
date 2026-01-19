<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Tests\Loyalty\Domain;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Account;
use SoftwareArchetypes\Accounting\Loyalty\Domain\AccountType;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Entry;
use SoftwareArchetypes\Accounting\Loyalty\Domain\EntryId;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Points;

/**
 * Chicago School TDD: Test Account behavior with real Entry objects.
 * Focus on balance calculation from entries (no stored balance!).
 */
final class AccountTest extends TestCase
{
    public function testCreatesAccountWithZeroBalance(): void
    {
        $account = Account::create(AccountType::ACTIVE_POINTS);

        self::assertSame(AccountType::ACTIVE_POINTS, $account->type());
        self::assertTrue($account->balance()->isZero());
        self::assertEmpty($account->entries());
    }

    public function testAddsEntryAndCalculatesBalance(): void
    {
        $account = Account::create(AccountType::ACTIVE_POINTS);

        $entry = Entry::create(
            EntryId::generate(),
            AccountType::ACTIVE_POINTS,
            Points::of(1000),
            new DateTimeImmutable(),
            'TXN-001',
            'Test entry'
        );

        $account->addEntry($entry);

        self::assertSame(1000, $account->balance()->amount());
        self::assertCount(1, $account->entries());
    }

    public function testBalanceIsSumOfAllEntries(): void
    {
        $account = Account::create(AccountType::ACTIVE_POINTS);

        // Add multiple entries
        $account->addEntry($this->createEntry(AccountType::ACTIVE_POINTS, 1000));
        $account->addEntry($this->createEntry(AccountType::ACTIVE_POINTS, 500));
        $account->addEntry($this->createEntry(AccountType::ACTIVE_POINTS, 250));

        // Balance = 1000 + 500 + 250 = 1750
        self::assertSame(1750, $account->balance()->amount());
    }

    public function testBalanceHandlesNegativeEntries(): void
    {
        $account = Account::create(AccountType::ACTIVE_POINTS);

        $account->addEntry($this->createEntry(AccountType::ACTIVE_POINTS, 1000));
        $account->addEntry($this->createEntry(AccountType::ACTIVE_POINTS, -300)); // Reversal
        $account->addEntry($this->createEntry(AccountType::ACTIVE_POINTS, -200)); // Deduction

        // Balance = 1000 - 300 - 200 = 500
        self::assertSame(500, $account->balance()->amount());
    }

    public function testCannotAddEntryWithWrongAccountType(): void
    {
        $account = Account::create(AccountType::ACTIVE_POINTS);

        $wrongEntry = Entry::create(
            EntryId::generate(),
            AccountType::PENDING_FROM_PURCHASES, // Wrong type!
            Points::of(100),
            new DateTimeImmutable(),
            'TXN-001',
            'Test'
        );

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('does not match account type');

        $account->addEntry($wrongEntry);
    }

    public function testFindsEntriesForReference(): void
    {
        $account = Account::create(AccountType::PENDING_FROM_PURCHASES);

        $account->addEntry($this->createEntryWithRef('PURCH-001', 'LINE-001', 500));
        $account->addEntry($this->createEntryWithRef('PURCH-001', 'LINE-002', 300));
        $account->addEntry($this->createEntryWithRef('PURCH-002', 'LINE-003', 100));

        $entriesForPurch1 = $account->entriesForReference('PURCH-001');

        self::assertCount(2, $entriesForPurch1);
        self::assertSame(500, $entriesForPurch1[0]->amount()->amount());
        self::assertSame(300, $entriesForPurch1[1]->amount()->amount());
    }

    public function testFindsEntriesForLineItem(): void
    {
        $account = Account::create(AccountType::PENDING_FROM_PURCHASES);

        $account->addEntry($this->createEntryWithRef('PURCH-001', 'LINE-001', 500));
        $account->addEntry($this->createEntryWithRef('PURCH-001', 'LINE-002', 300));

        $entriesForLine1 = $account->entriesForLineItem('LINE-001');

        self::assertCount(1, $entriesForLine1);
        self::assertSame(500, $entriesForLine1[0]->amount()->amount());
    }

    public function testCalculatesBalanceForReference(): void
    {
        $account = Account::create(AccountType::PENDING_FROM_PURCHASES);

        $account->addEntry($this->createEntryWithRef('PURCH-001', 'LINE-001', 500));
        $account->addEntry($this->createEntryWithRef('PURCH-001', 'LINE-002', 300));
        $account->addEntry($this->createEntryWithRef('PURCH-001', 'LINE-002', -100)); // Partial reversal
        $account->addEntry($this->createEntryWithRef('PURCH-002', 'LINE-003', 1000));

        $balanceForPurch1 = $account->balanceForReference('PURCH-001');

        // 500 + 300 - 100 = 700
        self::assertSame(700, $balanceForPurch1->amount());
    }

    public function testCalculatesBalanceForLineItem(): void
    {
        $account = Account::create(AccountType::PENDING_FROM_PURCHASES);

        $account->addEntry($this->createEntryWithRef('PURCH-001', 'LINE-001', 500));
        $account->addEntry($this->createEntryWithRef('PURCH-001', 'LINE-001', -200)); // Reversal
        $account->addEntry($this->createEntryWithRef('PURCH-001', 'LINE-002', 300));

        $balanceForLine1 = $account->balanceForLineItem('LINE-001');

        // 500 - 200 = 300
        self::assertSame(300, $balanceForLine1->amount());
    }

    private function createEntry(AccountType $type, int $amount): Entry
    {
        return Entry::create(
            EntryId::generate(),
            $type,
            Points::of($amount),
            new DateTimeImmutable(),
            'TXN-TEST',
            'Test entry'
        );
    }

    private function createEntryWithRef(string $ref, string $lineItem, int $amount): Entry
    {
        return Entry::create(
            EntryId::generate(),
            AccountType::PENDING_FROM_PURCHASES,
            Points::of($amount),
            new DateTimeImmutable(),
            'TXN-TEST',
            'Test entry',
            $ref,
            $lineItem
        );
    }
}
