<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Tests\Domain;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Accounting\AccountType;

class AccountTypeTest extends TestCase
{
    public function testAssetAccountTypeHasDoubleEntryBookingEnabled(): void
    {
        $this->assertTrue(AccountType::ASSET->isDoubleEntryBookingEnabled());
    }

    public function testExpenseAccountTypeHasDoubleEntryBookingEnabled(): void
    {
        $this->assertTrue(AccountType::EXPENSE->isDoubleEntryBookingEnabled());
    }

    public function testLiabilityAccountTypeHasDoubleEntryBookingEnabled(): void
    {
        $this->assertTrue(AccountType::LIABILITY->isDoubleEntryBookingEnabled());
    }

    public function testRevenueAccountTypeHasDoubleEntryBookingEnabled(): void
    {
        $this->assertTrue(AccountType::REVENUE->isDoubleEntryBookingEnabled());
    }

    public function testOffBalanceAccountTypeHasDoubleEntryBookingDisabled(): void
    {
        $this->assertFalse(AccountType::OFF_BALANCE->isDoubleEntryBookingEnabled());
    }

    public function testCanGetAccountTypeName(): void
    {
        $this->assertEquals('ASSET', AccountType::ASSET->name);
        $this->assertEquals('LIABILITY', AccountType::LIABILITY->name);
        $this->assertEquals('REVENUE', AccountType::REVENUE->name);
        $this->assertEquals('EXPENSE', AccountType::EXPENSE->name);
        $this->assertEquals('OFF_BALANCE', AccountType::OFF_BALANCE->name);
    }
}
