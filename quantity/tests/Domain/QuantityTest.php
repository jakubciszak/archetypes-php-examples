<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Quantity\Tests\Domain;

use Brick\Math\BigDecimal;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Quantity\Quantity;
use SoftwareArchetypes\Quantity\Unit;

final class QuantityTest extends TestCase
{
    public function testCanBeCreatedWithBigDecimalAmount(): void
    {
        $amount = BigDecimal::of('100.50');
        $unit = Unit::kilograms();

        $quantity = Quantity::of($amount, $unit);

        self::assertTrue($quantity->amount()->isEqualTo($amount));
        self::assertEquals($unit, $quantity->unit());
    }

    public function testCanBeCreatedWithIntAmount(): void
    {
        $quantity = Quantity::of(100, Unit::pieces());

        self::assertTrue($quantity->amount()->isEqualTo(BigDecimal::of(100)));
        self::assertEquals(Unit::pieces(), $quantity->unit());
    }

    public function testCanBeCreatedWithFloatAmount(): void
    {
        $quantity = Quantity::of(50.5, Unit::liters());

        self::assertTrue($quantity->amount()->isEqualTo(BigDecimal::of('50.5')));
        self::assertEquals(Unit::liters(), $quantity->unit());
    }

    public function testCanBeCreatedWithStringAmount(): void
    {
        $quantity = Quantity::of('99.99', Unit::meters());

        self::assertTrue($quantity->amount()->isEqualTo(BigDecimal::of('99.99')));
        self::assertEquals(Unit::meters(), $quantity->unit());
    }

    public function testThrowsExceptionWhenAmountIsNegative(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount cannot be negative');

        Quantity::of(-10, Unit::kilograms());
    }

    public function testAllowsZeroAmount(): void
    {
        $quantity = Quantity::of(0, Unit::pieces());

        self::assertTrue($quantity->amount()->isEqualTo(BigDecimal::zero()));
    }

    public function testCanAddQuantitiesWithSameUnit(): void
    {
        $q1 = Quantity::of(100, Unit::kilograms());
        $q2 = Quantity::of(50, Unit::kilograms());

        $result = $q1->add($q2);

        self::assertTrue($result->amount()->isEqualTo(BigDecimal::of(150)));
        self::assertEquals(Unit::kilograms(), $result->unit());
    }

    public function testThrowsExceptionWhenAddingDifferentUnits(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot add quantities with different units: kg and l');

        $q1 = Quantity::of(100, Unit::kilograms());
        $q2 = Quantity::of(50, Unit::liters());

        $q1->add($q2);
    }

    public function testCanSubtractQuantitiesWithSameUnit(): void
    {
        $q1 = Quantity::of(100, Unit::liters());
        $q2 = Quantity::of(30, Unit::liters());

        $result = $q1->subtract($q2);

        self::assertTrue($result->amount()->isEqualTo(BigDecimal::of(70)));
        self::assertEquals(Unit::liters(), $result->unit());
    }

    public function testThrowsExceptionWhenSubtractingDifferentUnits(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot subtract quantities with different units: m and l');

        $q1 = Quantity::of(100, Unit::meters());
        $q2 = Quantity::of(50, Unit::liters());

        $q1->subtract($q2);
    }

    public function testPreservesPrecisionInOperations(): void
    {
        $q1 = Quantity::of('0.000001', Unit::meters());
        $q2 = Quantity::of('0.000002', Unit::meters());

        $result = $q1->add($q2);

        self::assertTrue($result->amount()->isEqualTo(BigDecimal::of('0.000003')));
    }

    public function testToStringReturnsFormattedQuantity(): void
    {
        $quantity = Quantity::of(100, Unit::kilograms());

        self::assertEquals('100 kg', (string) $quantity);
    }

    public function testToStringWithDecimalAmount(): void
    {
        $quantity = Quantity::of('99.99', Unit::meters());

        self::assertEquals('99.99 m', (string) $quantity);
    }

    public function testEqualityBasedOnAmountAndUnit(): void
    {
        $q1 = Quantity::of(100, Unit::kilograms());
        $q2 = Quantity::of(100, Unit::kilograms());
        $q3 = Quantity::of(100, Unit::liters());
        $q4 = Quantity::of(50, Unit::kilograms());

        self::assertTrue($q1->equals($q2));
        self::assertFalse($q1->equals($q3));
        self::assertFalse($q1->equals($q4));
    }

    public function testHandlesLargeNumbers(): void
    {
        $quantity = Quantity::of('9999999999999.99', Unit::pieces());

        self::assertTrue($quantity->amount()->isEqualTo(BigDecimal::of('9999999999999.99')));
    }

    public function testHandlesSmallDecimals(): void
    {
        $quantity = Quantity::of('0.000001', Unit::meters());

        self::assertTrue($quantity->amount()->isEqualTo(BigDecimal::of('0.000001')));
    }

    public function testWorksWithAllPredefinedUnits(): void
    {
        $quantities = [
            Quantity::of(100, Unit::pieces()),
            Quantity::of(50, Unit::kilograms()),
            Quantity::of(25, Unit::liters()),
            Quantity::of(10, Unit::meters()),
            Quantity::of(5, Unit::squareMeters()),
            Quantity::of(2, Unit::cubicMeters()),
            Quantity::of(8, Unit::hours()),
            Quantity::of(30, Unit::minutes()),
        ];

        foreach ($quantities as $quantity) {
            self::assertInstanceOf(Quantity::class, $quantity);
        }
    }

    public function testWorksWithCustomUnits(): void
    {
        $customUnit = Unit::of('widget', 'widgets');
        $quantity = Quantity::of(100, $customUnit);

        self::assertEquals('100 widget', (string) $quantity);
    }

    public function testImmutabilityOnAdd(): void
    {
        $original = Quantity::of(100, Unit::kilograms());
        $toAdd = Quantity::of(50, Unit::kilograms());

        $result = $original->add($toAdd);

        self::assertTrue($original->amount()->isEqualTo(BigDecimal::of(100)));
        self::assertTrue($result->amount()->isEqualTo(BigDecimal::of(150)));
        self::assertNotSame($original, $result);
    }

    public function testImmutabilityOnSubtract(): void
    {
        $original = Quantity::of(100, Unit::liters());
        $toSubtract = Quantity::of(30, Unit::liters());

        $result = $original->subtract($toSubtract);

        self::assertTrue($original->amount()->isEqualTo(BigDecimal::of(100)));
        self::assertTrue($result->amount()->isEqualTo(BigDecimal::of(70)));
        self::assertNotSame($original, $result);
    }

    public function testIsZero(): void
    {
        $zero = Quantity::of(0, Unit::kilograms());
        $nonZero = Quantity::of(100, Unit::kilograms());

        self::assertTrue($zero->isZero());
        self::assertFalse($nonZero->isZero());
    }

    public function testIsGreaterThan(): void
    {
        $q1 = Quantity::of(100, Unit::kilograms());
        $q2 = Quantity::of(50, Unit::kilograms());

        self::assertTrue($q1->isGreaterThan($q2));
        self::assertFalse($q2->isGreaterThan($q1));
    }

    public function testIsGreaterThanThrowsExceptionForDifferentUnits(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot compare quantities with different units: kg and l');

        $q1 = Quantity::of(100, Unit::kilograms());
        $q2 = Quantity::of(50, Unit::liters());

        $q1->isGreaterThan($q2);
    }

    public function testIsLessThan(): void
    {
        $q1 = Quantity::of(50, Unit::kilograms());
        $q2 = Quantity::of(100, Unit::kilograms());

        self::assertTrue($q1->isLessThan($q2));
        self::assertFalse($q2->isLessThan($q1));
    }

    public function testIsLessThanThrowsExceptionForDifferentUnits(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Cannot compare quantities with different units: kg and l');

        $q1 = Quantity::of(100, Unit::kilograms());
        $q2 = Quantity::of(50, Unit::liters());

        $q1->isLessThan($q2);
    }
}
