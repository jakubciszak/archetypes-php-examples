<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Tests\Loyalty\Domain;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Accounting\Loyalty\Domain\AccountType;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Entry;
use SoftwareArchetypes\Accounting\Loyalty\Domain\EntryId;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Points;

/**
 * Chicago School TDD: Test Entry through its public API with real objects.
 */
final class EntryTest extends TestCase
{
    public function testCreatesEntryWithAllProperties(): void
    {
        $entryId = EntryId::generate();
        $points = Points::of(1000);
        $date = new DateTimeImmutable('2024-01-01');

        $entry = Entry::create(
            $entryId,
            AccountType::PENDING_FROM_PURCHASES,
            $points,
            $date,
            'TXN-001',
            'Purchase PURCH-001',
            'PURCH-001',
            'LINE-001',
            ['market' => 'PL']
        );

        self::assertTrue($entry->id()->equals($entryId));
        self::assertSame(AccountType::PENDING_FROM_PURCHASES, $entry->accountType());
        self::assertTrue($entry->amount()->equals($points));
        self::assertSame($date, $entry->effectiveDate());
        self::assertSame('TXN-001', $entry->transactionId());
        self::assertSame('Purchase PURCH-001', $entry->description());
        self::assertSame('PURCH-001', $entry->referenceId());
        self::assertSame('LINE-001', $entry->lineItemId());
        self::assertSame(['market' => 'PL'], $entry->metadata());
    }

    public function testEntryCanHaveNegativeAmount(): void
    {
        // Entry-based ledger: negative amounts represent reversals/deductions
        $entry = Entry::create(
            EntryId::generate(),
            AccountType::ACTIVE_POINTS,
            Points::of(-500), // Negative!
            new DateTimeImmutable(),
            'TXN-REVERSAL',
            'Return reversal',
        );

        self::assertSame(-500, $entry->amount()->amount());
    }

    public function testChecksIfEntryIsForReference(): void
    {
        $entry = Entry::create(
            EntryId::generate(),
            AccountType::PENDING_FROM_PURCHASES,
            Points::of(100),
            new DateTimeImmutable(),
            'TXN-001',
            'Test',
            'PURCH-123'
        );

        self::assertTrue($entry->isForReference('PURCH-123'));
        self::assertFalse($entry->isForReference('PURCH-999'));
    }

    public function testChecksIfEntryIsForLineItem(): void
    {
        $entry = Entry::create(
            EntryId::generate(),
            AccountType::PENDING_FROM_PURCHASES,
            Points::of(100),
            new DateTimeImmutable(),
            'TXN-001',
            'Test',
            'PURCH-123',
            'LINE-001'
        );

        self::assertTrue($entry->isForLineItem('LINE-001'));
        self::assertFalse($entry->isForLineItem('LINE-999'));
    }
}
