<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Quantity\Tests\Domain;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Quantity\Unit;

final class UnitTest extends TestCase
{
    public function testCanBeCreatedWithSymbolAndName(): void
    {
        $unit = Unit::of('kg', 'kilograms');

        self::assertEquals('kg', $unit->symbol());
        self::assertEquals('kilograms', $unit->name());
    }

    public function testThrowsExceptionWhenSymbolIsNull(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unit symbol cannot be null or blank');

        Unit::of('', 'kilograms');
    }

    public function testThrowsExceptionWhenSymbolIsBlank(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unit symbol cannot be null or blank');

        Unit::of('   ', 'kilograms');
    }

    public function testThrowsExceptionWhenNameIsNull(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unit name cannot be null or blank');

        Unit::of('kg', '');
    }

    public function testThrowsExceptionWhenNameIsBlank(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unit name cannot be null or blank');

        Unit::of('kg', '   ');
    }

    public function testPiecesFactoryMethod(): void
    {
        $unit = Unit::pieces();

        self::assertEquals('pcs', $unit->symbol());
        self::assertEquals('pieces', $unit->name());
    }

    public function testKilogramsFactoryMethod(): void
    {
        $unit = Unit::kilograms();

        self::assertEquals('kg', $unit->symbol());
        self::assertEquals('kilograms', $unit->name());
    }

    public function testLitersFactoryMethod(): void
    {
        $unit = Unit::liters();

        self::assertEquals('l', $unit->symbol());
        self::assertEquals('liters', $unit->name());
    }

    public function testMetersFactoryMethod(): void
    {
        $unit = Unit::meters();

        self::assertEquals('m', $unit->symbol());
        self::assertEquals('meters', $unit->name());
    }

    public function testSquareMetersFactoryMethod(): void
    {
        $unit = Unit::squareMeters();

        self::assertEquals('m²', $unit->symbol());
        self::assertEquals('square meters', $unit->name());
    }

    public function testCubicMetersFactoryMethod(): void
    {
        $unit = Unit::cubicMeters();

        self::assertEquals('m³', $unit->symbol());
        self::assertEquals('cubic meters', $unit->name());
    }

    public function testHoursFactoryMethod(): void
    {
        $unit = Unit::hours();

        self::assertEquals('h', $unit->symbol());
        self::assertEquals('hours', $unit->name());
    }

    public function testMinutesFactoryMethod(): void
    {
        $unit = Unit::minutes();

        self::assertEquals('min', $unit->symbol());
        self::assertEquals('minutes', $unit->name());
    }

    public function testToStringReturnsSymbol(): void
    {
        $unit = Unit::of('kg', 'kilograms');

        self::assertEquals('kg', (string) $unit);
    }

    public function testEqualityBasedOnSymbolAndName(): void
    {
        $unit1 = Unit::of('kg', 'kilograms');
        $unit2 = Unit::of('kg', 'kilograms');
        $unit3 = Unit::of('l', 'liters');

        self::assertEquals($unit1, $unit2);
        self::assertNotEquals($unit1, $unit3);
    }

    public function testSupportsUnicodeSymbols(): void
    {
        $celsius = Unit::of('℃', 'degrees Celsius');
        $ohm = Unit::of('Ω', 'ohm');

        self::assertEquals('℃', $celsius->symbol());
        self::assertEquals('Ω', $ohm->symbol());
    }

    public function testCustomUnitsCanBeCreated(): void
    {
        $custom = Unit::of('widget', 'widgets');

        self::assertEquals('widget', $custom->symbol());
        self::assertEquals('widgets', $custom->name());
    }
}
