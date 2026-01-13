<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Tests\Domain;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Accounting\Money;
use SoftwareArchetypes\Accounting\AccountCredited;
use SoftwareArchetypes\Accounting\AccountDebited;
use SoftwareArchetypes\Accounting\AccountId;
use SoftwareArchetypes\Accounting\EntryId;
use SoftwareArchetypes\Accounting\TransactionId;

class EntryTest extends TestCase
{
    private EntryId $entryId;
    private TransactionId $transactionId;
    private AccountId $accountId;
    private Money $amount;
    private DateTimeImmutable $occurredAt;
    private DateTimeImmutable $appliesAt;

    protected function setUp(): void
    {
        $this->entryId = EntryId::generate();
        $this->transactionId = TransactionId::generate();
        $this->accountId = AccountId::fromString('acc-123');
        $this->amount = Money::of(100);
        $this->occurredAt = new DateTimeImmutable('2024-01-01 10:00:00');
        $this->appliesAt = new DateTimeImmutable('2024-01-01 10:00:00');
    }

    public function testCanCreateDebitEntry(): void
    {
        $entry = new AccountDebited(
            $this->entryId,
            $this->transactionId,
            $this->occurredAt,
            $this->appliesAt,
            $this->accountId,
            $this->amount,
        );

        $this->assertTrue($this->entryId->equals($entry->id()));
        $this->assertTrue($this->transactionId->equals($entry->transactionId()));
        $this->assertEquals($this->occurredAt, $entry->occurredAt());
        $this->assertEquals($this->appliesAt, $entry->appliesAt());
        $this->assertTrue($this->accountId->equals($entry->accountId()));
    }

    public function testDebitEntryAmountIsNegated(): void
    {
        $entry = new AccountDebited(
            $this->entryId,
            $this->transactionId,
            $this->occurredAt,
            $this->appliesAt,
            $this->accountId,
            Money::of(100),
        );

        // Debit entries should have negative amounts
        $this->assertEquals(-100, $entry->amount()->amount());
    }

    public function testCanCreateCreditEntry(): void
    {
        $entry = new AccountCredited(
            $this->entryId,
            $this->transactionId,
            $this->occurredAt,
            $this->appliesAt,
            $this->accountId,
            $this->amount,
        );

        $this->assertTrue($this->entryId->equals($entry->id()));
        $this->assertTrue($this->transactionId->equals($entry->transactionId()));
        $this->assertEquals($this->occurredAt, $entry->occurredAt());
        $this->assertEquals($this->appliesAt, $entry->appliesAt());
        $this->assertTrue($this->accountId->equals($entry->accountId()));
    }

    public function testCreditEntryAmountRemainsPositive(): void
    {
        $entry = new AccountCredited(
            $this->entryId,
            $this->transactionId,
            $this->occurredAt,
            $this->appliesAt,
            $this->accountId,
            Money::of(100),
        );

        // Credit entries should keep positive amounts
        $this->assertEquals(100, $entry->amount()->amount());
    }

    public function testDebitAndCreditImplementSameInterface(): void
    {
        $debit = new AccountDebited(
            $this->entryId,
            $this->transactionId,
            $this->occurredAt,
            $this->appliesAt,
            $this->accountId,
            $this->amount,
        );

        $credit = new AccountCredited(
            EntryId::generate(),
            $this->transactionId,
            $this->occurredAt,
            $this->appliesAt,
            $this->accountId,
            $this->amount,
        );

        $this->assertInstanceOf(\SoftwareArchetypes\Accounting\Entry::class, $debit);
        $this->assertInstanceOf(\SoftwareArchetypes\Accounting\Entry::class, $credit);
    }
}
