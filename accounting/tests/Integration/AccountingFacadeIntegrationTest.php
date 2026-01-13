<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Tests\Integration;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Accounting\Application\AccountingFacade;
use SoftwareArchetypes\Accounting\Common\Money;
use SoftwareArchetypes\Accounting\Domain\AccountId;
use SoftwareArchetypes\Accounting\Domain\AccountType;
use SoftwareArchetypes\Accounting\Events\CreditEntryRegistered;
use SoftwareArchetypes\Accounting\Events\DebitEntryRegistered;
use SoftwareArchetypes\Accounting\Infrastructure\InMemoryAccountRepository;
use SoftwareArchetypes\Accounting\Infrastructure\InMemoryEventsPublisher;

class AccountingFacadeIntegrationTest extends TestCase
{
    private AccountingFacade $facade;
    private InMemoryEventsPublisher $eventsPublisher;

    protected function setUp(): void
    {
        $accountRepository = new InMemoryAccountRepository();
        $this->eventsPublisher = new InMemoryEventsPublisher();
        $this->facade = new AccountingFacade($accountRepository, $this->eventsPublisher);
    }

    public function testCanCreateAccountWithInitialBalance(): void
    {
        $accountId = AccountId::generate();

        $account = $this->facade->createAccount(
            $accountId,
            AccountType::ASSET,
            'Cash Account',
            Money::of(1000),
        );

        $this->assertEquals(1000, $account->balance()->amount());
    }

    public function testCanFindCreatedAccount(): void
    {
        $accountId = AccountId::generate();

        $this->facade->createAccount(
            $accountId,
            AccountType::ASSET,
            'Savings',
            Money::of(5000),
        );

        $found = $this->facade->findAccount($accountId);

        $this->assertNotNull($found);
        $this->assertTrue($accountId->equals($found->id()));
        $this->assertEquals(5000, $found->balance()->amount());
    }

    public function testCanGetAccountBalance(): void
    {
        $accountId = AccountId::generate();

        $this->facade->createAccount(
            $accountId,
            AccountType::LIABILITY,
            'Credit Card',
            Money::of(2500),
        );

        $balance = $this->facade->balance($accountId);

        $this->assertNotNull($balance);
        $this->assertEquals(2500, $balance->amount());
    }

    public function testCanFindAllAccounts(): void
    {
        $this->facade->createAccount(
            AccountId::generate(),
            AccountType::ASSET,
            'Cash',
            Money::of(1000),
        );

        $this->facade->createAccount(
            AccountId::generate(),
            AccountType::REVENUE,
            'Sales',
        );

        $accounts = $this->facade->findAllAccounts();

        $this->assertCount(2, $accounts);
    }

    public function testTransferMovesMoneyBetweenAccounts(): void
    {
        $checkingId = AccountId::fromString('checking-123');
        $savingsId = AccountId::fromString('savings-456');

        $this->facade->createAccount(
            $checkingId,
            AccountType::ASSET,
            'Checking',
            Money::of(1000),
        );

        $this->facade->createAccount(
            $savingsId,
            AccountType::ASSET,
            'Savings',
            Money::of(500),
        );

        $this->facade->transfer(
            $checkingId,
            $savingsId,
            Money::of(300),
            new DateTimeImmutable(),
        );

        $checkingBalance = $this->facade->balance($checkingId);
        $savingsBalance = $this->facade->balance($savingsId);

        $this->assertEquals(700, $checkingBalance?->amount());
        $this->assertEquals(800, $savingsBalance?->amount());
    }

    public function testTransferPublishesEventsForBothAccounts(): void
    {
        $fromId = AccountId::generate();
        $toId = AccountId::generate();

        $this->facade->createAccount($fromId, AccountType::ASSET, 'From', Money::of(1000));
        $this->facade->createAccount($toId, AccountType::ASSET, 'To', Money::of(0));

        $this->eventsPublisher->clear();

        $this->facade->transfer(
            $fromId,
            $toId,
            Money::of(250),
            new DateTimeImmutable(),
        );

        $events = $this->eventsPublisher->getPublishedEvents();

        $this->assertCount(2, $events);
        $this->assertInstanceOf(DebitEntryRegistered::class, $events[0]);
        $this->assertInstanceOf(CreditEntryRegistered::class, $events[1]);
    }
}
