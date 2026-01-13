<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Tests\Domain;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Accounting\Money;
use SoftwareArchetypes\Accounting\Account;
use SoftwareArchetypes\Accounting\AccountCredited;
use SoftwareArchetypes\Accounting\AccountDebited;
use SoftwareArchetypes\Accounting\AccountId;
use SoftwareArchetypes\Accounting\AccountType;
use SoftwareArchetypes\Accounting\EntryId;
use SoftwareArchetypes\Accounting\TransactionId;
use SoftwareArchetypes\Accounting\Events\CreditEntryRegistered;
use SoftwareArchetypes\Accounting\Events\DebitEntryRegistered;

class AccountTest extends TestCase
{
    private AccountId $accountId;
    private TransactionId $transactionId;

    protected function setUp(): void
    {
        $this->accountId = AccountId::fromString('acc-123');
        $this->transactionId = TransactionId::fromString('txn-456');
    }

    public function testCanCreateAccountWithInitialBalance(): void
    {
        $account = Account::create(
            $this->accountId,
            AccountType::ASSET,
            'Cash Account',
            Money::of(1000),
        );

        $this->assertTrue($this->accountId->equals($account->id()));
        $this->assertEquals(AccountType::ASSET, $account->type());
        $this->assertTrue(Money::of(1000)->equals($account->balance()));
    }

    public function testCanCreateAccountWithZeroBalance(): void
    {
        $account = Account::create(
            $this->accountId,
            AccountType::EXPENSE,
            'Utilities',
        );

        $this->assertTrue(Money::zero()->equals($account->balance()));
    }

    public function testAddingCreditEntryIncreasesBalance(): void
    {
        $account = Account::create(
            $this->accountId,
            AccountType::ASSET,
            'Cash',
            Money::of(100),
        );

        $entry = new AccountCredited(
            EntryId::generate(),
            $this->transactionId,
            new DateTimeImmutable(),
            new DateTimeImmutable(),
            $this->accountId,
            Money::of(50),
        );

        $account->addEntry($entry);

        $this->assertEquals(150, $account->balance()->amount());
    }

    public function testAddingDebitEntryDecreasesBalance(): void
    {
        $account = Account::create(
            $this->accountId,
            AccountType::ASSET,
            'Cash',
            Money::of(100),
        );

        $entry = new AccountDebited(
            EntryId::generate(),
            $this->transactionId,
            new DateTimeImmutable(),
            new DateTimeImmutable(),
            $this->accountId,
            Money::of(30),
        );

        $account->addEntry($entry);

        $this->assertEquals(70, $account->balance()->amount());
    }

    public function testAddingMultipleEntriesUpdatesBalance(): void
    {
        $account = Account::create(
            $this->accountId,
            AccountType::ASSET,
            'Cash',
            Money::of(100),
        );

        $entries = [
            new AccountCredited(
                EntryId::generate(),
                $this->transactionId,
                new DateTimeImmutable(),
                new DateTimeImmutable(),
                $this->accountId,
                Money::of(50),
            ),
            new AccountDebited(
                EntryId::generate(),
                $this->transactionId,
                new DateTimeImmutable(),
                new DateTimeImmutable(),
                $this->accountId,
                Money::of(20),
            ),
        ];

        $account->addEntries($entries);

        // 100 + 50 - 20 = 130
        $this->assertEquals(130, $account->balance()->amount());
    }

    public function testAddingCreditEntryPublishesCreditEvent(): void
    {
        $account = Account::create(
            $this->accountId,
            AccountType::REVENUE,
            'Sales',
        );

        $entry = new AccountCredited(
            EntryId::generate(),
            $this->transactionId,
            new DateTimeImmutable(),
            new DateTimeImmutable(),
            $this->accountId,
            Money::of(200),
        );

        $account->addEntry($entry);

        $events = $account->pendingEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(CreditEntryRegistered::class, $events[0]);
    }

    public function testAddingDebitEntryPublishesDebitEvent(): void
    {
        $account = Account::create(
            $this->accountId,
            AccountType::EXPENSE,
            'Rent',
        );

        $entry = new AccountDebited(
            EntryId::generate(),
            $this->transactionId,
            new DateTimeImmutable(),
            new DateTimeImmutable(),
            $this->accountId,
            Money::of(500),
        );

        $account->addEntry($entry);

        $events = $account->pendingEvents();
        $this->assertCount(1, $events);
        $this->assertInstanceOf(DebitEntryRegistered::class, $events[0]);
    }

    public function testClearPendingEventsRemovesAllEvents(): void
    {
        $account = Account::create(
            $this->accountId,
            AccountType::ASSET,
            'Cash',
        );

        $entry = new AccountCredited(
            EntryId::generate(),
            $this->transactionId,
            new DateTimeImmutable(),
            new DateTimeImmutable(),
            $this->accountId,
            Money::of(100),
        );

        $account->addEntry($entry);
        $this->assertCount(1, $account->pendingEvents());

        $account->clearPendingEvents();
        $this->assertCount(0, $account->pendingEvents());
    }

    public function testGetEntriesReturnsDefensiveCopy(): void
    {
        $account = Account::create(
            $this->accountId,
            AccountType::ASSET,
            'Cash',
        );

        $entry = new AccountCredited(
            EntryId::generate(),
            $this->transactionId,
            new DateTimeImmutable(),
            new DateTimeImmutable(),
            $this->accountId,
            Money::of(100),
        );

        $account->addEntry($entry);

        $entries = $account->entries();
        $this->assertCount(1, $entries);
    }
}
