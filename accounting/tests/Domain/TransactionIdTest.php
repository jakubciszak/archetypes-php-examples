<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Tests\Domain;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Accounting\Domain\TransactionId;

class TransactionIdTest extends TestCase
{
    public function testCanCreateTransactionIdFromString(): void
    {
        $id = TransactionId::fromString('txn-123');

        $this->assertEquals('txn-123', $id->toString());
    }

    public function testCanGenerateRandomTransactionId(): void
    {
        $id1 = TransactionId::generate();
        $id2 = TransactionId::generate();

        $this->assertNotEquals($id1->toString(), $id2->toString());
        $this->assertNotEmpty($id1->toString());
    }

    public function testTwoTransactionIdsWithSameValueAreEqual(): void
    {
        $id1 = TransactionId::fromString('txn-123');
        $id2 = TransactionId::fromString('txn-123');

        $this->assertTrue($id1->equals($id2));
    }

    public function testCannotCreateTransactionIdWithEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Transaction ID cannot be empty');

        TransactionId::fromString('');
    }
}
