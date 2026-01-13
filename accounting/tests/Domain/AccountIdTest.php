<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Tests\Domain;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Accounting\Domain\AccountId;

class AccountIdTest extends TestCase
{
    public function testCanCreateAccountIdFromString(): void
    {
        $id = AccountId::fromString('acc-123');

        $this->assertEquals('acc-123', $id->toString());
    }

    public function testCanGenerateRandomAccountId(): void
    {
        $id1 = AccountId::generate();
        $id2 = AccountId::generate();

        $this->assertNotEquals($id1->toString(), $id2->toString());
        $this->assertNotEmpty($id1->toString());
    }

    public function testTwoAccountIdsWithSameValueAreEqual(): void
    {
        $id1 = AccountId::fromString('acc-123');
        $id2 = AccountId::fromString('acc-123');

        $this->assertTrue($id1->equals($id2));
    }

    public function testTwoAccountIdsWithDifferentValuesAreNotEqual(): void
    {
        $id1 = AccountId::fromString('acc-123');
        $id2 = AccountId::fromString('acc-456');

        $this->assertFalse($id1->equals($id2));
    }

    public function testCannotCreateAccountIdWithEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Account ID cannot be empty');

        AccountId::fromString('');
    }
}
