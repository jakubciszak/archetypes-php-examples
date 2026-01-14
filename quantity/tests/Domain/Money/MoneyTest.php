<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Quantity\Tests\Domain\Money;

use Brick\Math\BigDecimal;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Quantity\Money\Money;

final class MoneyTest extends TestCase
{
    public function testCanBeCreatedWithIntegerAmount(): void
    {
        $money = Money::pln(100);

        self::assertTrue($money->value()->isEqualTo(BigDecimal::of(100)));
        self::assertEquals('PLN', $money->currency());
    }

    public function testCanBeCreatedWithBigDecimalAmount(): void
    {
        $money = Money::pln(BigDecimal::of('99.99'));

        self::assertTrue($money->value()->isEqualTo(BigDecimal::of('99.99')));
    }

    public function testCanBeCreatedWithNumericAmount(): void
    {
        $money = Money::pln(50.5);

        self::assertTrue($money->value()->isEqualTo(BigDecimal::of('50.5')));
    }

    public function testZeroPlnFactory(): void
    {
        $money = Money::zeroPln();

        self::assertTrue($money->value()->isEqualTo(BigDecimal::zero()));
        self::assertEquals('PLN', $money->currency());
    }

    public function testOnePlnFactory(): void
    {
        $money = Money::onePln();

        self::assertTrue($money->value()->isEqualTo(BigDecimal::of(1)));
        self::assertEquals('PLN', $money->currency());
    }

    public function testCanAddMoney(): void
    {
        $m1 = Money::pln(100);
        $m2 = Money::pln(50);

        $result = $m1->add($m2);

        self::assertTrue($result->value()->isEqualTo(BigDecimal::of(150)));
    }

    public function testCanSubtractMoney(): void
    {
        $m1 = Money::pln(100);
        $m2 = Money::pln(25);

        $result = $m1->subtract($m2);

        self::assertTrue($result->value()->isEqualTo(BigDecimal::of(75)));
    }

    public function testCanNegateMoney(): void
    {
        $money = Money::pln(50);

        $result = $money->negate();

        self::assertTrue($result->value()->isEqualTo(BigDecimal::of(-50)));
    }

    public function testCanGetAbsoluteValue(): void
    {
        $negative = Money::pln(-50);

        $result = $negative->abs();

        self::assertTrue($result->value()->isEqualTo(BigDecimal::of(50)));
    }

    public function testIsZero(): void
    {
        $zero = Money::pln(0);
        $nonZero = Money::pln(100);

        self::assertTrue($zero->isZero());
        self::assertFalse($nonZero->isZero());
    }

    public function testIsNegative(): void
    {
        $negative = Money::pln(-10);
        $positive = Money::pln(10);
        $zero = Money::pln(0);

        self::assertTrue($negative->isNegative());
        self::assertFalse($positive->isNegative());
        self::assertFalse($zero->isNegative());
    }

    public function testIsGreaterThan(): void
    {
        $m1 = Money::pln(100);
        $m2 = Money::pln(50);

        self::assertTrue($m1->isGreaterThan($m2));
        self::assertFalse($m2->isGreaterThan($m1));
    }

    public function testIsGreaterThanOrEqualTo(): void
    {
        $m1 = Money::pln(100);
        $m2 = Money::pln(100);
        $m3 = Money::pln(50);

        self::assertTrue($m1->isGreaterThanOrEqualTo($m2));
        self::assertTrue($m1->isGreaterThanOrEqualTo($m3));
        self::assertFalse($m3->isGreaterThanOrEqualTo($m1));
    }

    public function testMinOfTwo(): void
    {
        $m1 = Money::pln(100);
        $m2 = Money::pln(50);

        $result = Money::min($m1, $m2);

        self::assertTrue($result->value()->isEqualTo(BigDecimal::of(50)));
    }

    public function testMaxOfTwo(): void
    {
        $m1 = Money::pln(100);
        $m2 = Money::pln(50);

        $result = Money::max($m1, $m2);

        self::assertTrue($result->value()->isEqualTo(BigDecimal::of(100)));
    }

    public function testCompareTo(): void
    {
        $m1 = Money::pln(100);
        $m2 = Money::pln(50);
        $m3 = Money::pln(100);

        self::assertGreaterThan(0, $m1->compareTo($m2));
        self::assertLessThan(0, $m2->compareTo($m1));
        self::assertEquals(0, $m1->compareTo($m3));
    }

    public function testEquals(): void
    {
        $m1 = Money::pln(100);
        $m2 = Money::pln(100);
        $m3 = Money::pln(50);

        self::assertTrue($m1->equals($m2));
        self::assertFalse($m1->equals($m3));
    }

    public function testToString(): void
    {
        $money = Money::pln(100);

        self::assertEquals('PLN 100', (string) $money);
    }

    public function testToStringWithDecimals(): void
    {
        $money = Money::pln(BigDecimal::of('99.99'));

        self::assertEquals('PLN 99.99', (string) $money);
    }

    public function testImmutabilityOnAdd(): void
    {
        $original = Money::pln(100);
        $toAdd = Money::pln(50);

        $result = $original->add($toAdd);

        self::assertTrue($original->value()->isEqualTo(BigDecimal::of(100)));
        self::assertTrue($result->value()->isEqualTo(BigDecimal::of(150)));
        self::assertNotSame($original, $result);
    }

    public function testImmutabilityOnSubtract(): void
    {
        $original = Money::pln(100);
        $toSubtract = Money::pln(30);

        $result = $original->subtract($toSubtract);

        self::assertTrue($original->value()->isEqualTo(BigDecimal::of(100)));
        self::assertTrue($result->value()->isEqualTo(BigDecimal::of(70)));
        self::assertNotSame($original, $result);
    }

    public function testCanHandleNegativeAmounts(): void
    {
        $money = Money::pln(-100);

        self::assertTrue($money->value()->isEqualTo(BigDecimal::of(-100)));
        self::assertTrue($money->isNegative());
    }

    public function testPreservesPrecision(): void
    {
        $m1 = Money::pln(BigDecimal::of('0.01'));
        $m2 = Money::pln(BigDecimal::of('0.02'));

        $result = $m1->add($m2);

        self::assertTrue($result->value()->isEqualTo(BigDecimal::of('0.03')));
    }

    public function testMultiply(): void
    {
        $money = Money::pln(10);

        $result = $money->multiply(3);

        self::assertTrue($result->value()->isEqualTo(BigDecimal::of(30)));
    }

    public function testMultiplyWithDecimal(): void
    {
        $money = Money::pln(100);

        $result = $money->multiply('1.5');

        self::assertTrue($result->value()->isEqualTo(BigDecimal::of(150)));
    }

    public function testDivide(): void
    {
        $money = Money::pln(100);

        $result = $money->divide(4);

        self::assertTrue($result->value()->isEqualTo(BigDecimal::of(25)));
    }

    public function testDivideWithRounding(): void
    {
        $money = Money::pln(100);

        $result = $money->divide(3);

        // Should round to 2 decimal places
        self::assertTrue($result->value()->isEqualTo(BigDecimal::of('33.33')));
    }

    public function testThrowsExceptionWhenDividingByZero(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot divide by zero');

        $money = Money::pln(100);
        $money->divide(0);
    }
}
