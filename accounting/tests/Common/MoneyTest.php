<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Tests\Common;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Accounting\Common\Money;

class MoneyTest extends TestCase
{
    public function testCanCreateMoneyWithIntegerAmount(): void
    {
        $money = Money::of(100);

        $this->assertEquals(100, $money->amount());
    }

    public function testCanCreateMoneyWithFloatAmount(): void
    {
        $money = Money::of(100.50);

        $this->assertEquals(100.50, $money->amount());
    }

    public function testCanCreateZeroMoney(): void
    {
        $money = Money::zero();

        $this->assertEquals(0, $money->amount());
    }

    public function testCanAddMoney(): void
    {
        $money1 = Money::of(100);
        $money2 = Money::of(50);

        $result = $money1->add($money2);

        $this->assertEquals(150, $result->amount());
    }

    public function testCanSubtractMoney(): void
    {
        $money1 = Money::of(100);
        $money2 = Money::of(30);

        $result = $money1->subtract($money2);

        $this->assertEquals(70, $result->amount());
    }

    public function testCanNegateMoney(): void
    {
        $money = Money::of(100);

        $result = $money->negate();

        $this->assertEquals(-100, $result->amount());
    }

    public function testNegateNegativeMoneyMakesItPositive(): void
    {
        $money = Money::of(-50);

        $result = $money->negate();

        $this->assertEquals(50, $result->amount());
    }

    public function testIsZeroReturnsTrueForZeroAmount(): void
    {
        $money = Money::zero();

        $this->assertTrue($money->isZero());
    }

    public function testIsZeroReturnsFalseForNonZeroAmount(): void
    {
        $money = Money::of(100);

        $this->assertFalse($money->isZero());
    }

    public function testIsPositiveReturnsTrueForPositiveAmount(): void
    {
        $money = Money::of(100);

        $this->assertTrue($money->isPositive());
    }

    public function testIsPositiveReturnsFalseForZero(): void
    {
        $money = Money::zero();

        $this->assertFalse($money->isPositive());
    }

    public function testIsPositiveReturnsFalseForNegativeAmount(): void
    {
        $money = Money::of(-100);

        $this->assertFalse($money->isPositive());
    }

    public function testIsNegativeReturnsTrueForNegativeAmount(): void
    {
        $money = Money::of(-100);

        $this->assertTrue($money->isNegative());
    }

    public function testIsNegativeReturnsFalseForPositiveAmount(): void
    {
        $money = Money::of(100);

        $this->assertFalse($money->isNegative());
    }

    public function testTwoMoneyObjectsWithSameAmountAreEqual(): void
    {
        $money1 = Money::of(100);
        $money2 = Money::of(100);

        $this->assertTrue($money1->equals($money2));
    }

    public function testTwoMoneyObjectsWithDifferentAmountsAreNotEqual(): void
    {
        $money1 = Money::of(100);
        $money2 = Money::of(200);

        $this->assertFalse($money1->equals($money2));
    }

    public function testCompareTo(): void
    {
        $money1 = Money::of(100);
        $money2 = Money::of(200);
        $money3 = Money::of(100);

        $this->assertLessThan(0, $money1->compareTo($money2));
        $this->assertGreaterThan(0, $money2->compareTo($money1));
        $this->assertEquals(0, $money1->compareTo($money3));
    }
}
