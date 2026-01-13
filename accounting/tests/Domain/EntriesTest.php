<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Tests\Domain;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Accounting\Common\Money;
use SoftwareArchetypes\Accounting\Domain\AccountCredited;
use SoftwareArchetypes\Accounting\Domain\AccountDebited;
use SoftwareArchetypes\Accounting\Domain\AccountId;
use SoftwareArchetypes\Accounting\Domain\Entries;
use SoftwareArchetypes\Accounting\Domain\EntryId;
use SoftwareArchetypes\Accounting\Domain\TransactionId;

class EntriesTest extends TestCase
{
    public function testCanCreateEmptyEntries(): void
    {
        $entries = Entries::empty();

        $this->assertCount(0, $entries->toList());
    }

    public function testCanAddSingleEntry(): void
    {
        $entries = Entries::empty();
        $entry = new AccountCredited(
            EntryId::generate(),
            TransactionId::generate(),
            new DateTimeImmutable(),
            new DateTimeImmutable(),
            AccountId::fromString('acc-1'),
            Money::of(100),
        );

        $entries->add($entry);

        $this->assertCount(1, $entries->toList());
    }

    public function testCanAddMultipleEntries(): void
    {
        $entries = Entries::empty();
        $entry1 = new AccountCredited(
            EntryId::generate(),
            TransactionId::generate(),
            new DateTimeImmutable(),
            new DateTimeImmutable(),
            AccountId::fromString('acc-1'),
            Money::of(100),
        );
        $entry2 = new AccountDebited(
            EntryId::generate(),
            TransactionId::generate(),
            new DateTimeImmutable(),
            new DateTimeImmutable(),
            AccountId::fromString('acc-2'),
            Money::of(50),
        );

        $entries->addAll([$entry1, $entry2]);

        $this->assertCount(2, $entries->toList());
    }

    public function testBalanceAsOfCalculatesCorrectBalance(): void
    {
        $now = new DateTimeImmutable('2024-01-01 12:00:00');
        $future = new DateTimeImmutable('2024-01-02 12:00:00');

        $entries = Entries::empty();
        $entries->add(new AccountCredited(
            EntryId::generate(),
            TransactionId::generate(),
            $now,
            $now,
            AccountId::fromString('acc-1'),
            Money::of(100),
        ));
        $entries->add(new AccountDebited(
            EntryId::generate(),
            TransactionId::generate(),
            $now,
            $now,
            AccountId::fromString('acc-1'),
            Money::of(30),
        ));

        $balance = $entries->balanceAsOf($now);

        // 100 (credit) - 30 (debit) = 70
        $this->assertEquals(70, $balance->amount());
    }

    public function testBalanceAsOfExcludesFutureEntries(): void
    {
        $past = new DateTimeImmutable('2024-01-01 12:00:00');
        $future = new DateTimeImmutable('2024-01-02 12:00:00');

        $entries = Entries::empty();
        $entries->add(new AccountCredited(
            EntryId::generate(),
            TransactionId::generate(),
            $past,
            $past,
            AccountId::fromString('acc-1'),
            Money::of(100),
        ));
        $entries->add(new AccountCredited(
            EntryId::generate(),
            TransactionId::generate(),
            $future,
            $future,
            AccountId::fromString('acc-1'),
            Money::of(50),
        ));

        $balance = $entries->balanceAsOf($past);

        // Only the first entry should be included
        $this->assertEquals(100, $balance->amount());
    }

    public function testCopyCreatesNewInstanceWithSameEntries(): void
    {
        $entries = Entries::empty();
        $entry = new AccountCredited(
            EntryId::generate(),
            TransactionId::generate(),
            new DateTimeImmutable(),
            new DateTimeImmutable(),
            AccountId::fromString('acc-1'),
            Money::of(100),
        );
        $entries->add($entry);

        $copy = $entries->copy();

        $this->assertNotSame($entries, $copy);
        $this->assertCount(1, $copy->toList());
    }

    public function testAmountsReturnsAllEntryAmounts(): void
    {
        $entries = Entries::empty();
        $entries->add(new AccountCredited(
            EntryId::generate(),
            TransactionId::generate(),
            new DateTimeImmutable(),
            new DateTimeImmutable(),
            AccountId::fromString('acc-1'),
            Money::of(100),
        ));
        $entries->add(new AccountDebited(
            EntryId::generate(),
            TransactionId::generate(),
            new DateTimeImmutable(),
            new DateTimeImmutable(),
            AccountId::fromString('acc-2'),
            Money::of(50),
        ));

        $amounts = $entries->amounts();

        $this->assertCount(2, $amounts);
        $this->assertEquals(100, $amounts[0]->amount());
        $this->assertEquals(-50, $amounts[1]->amount());
    }
}
