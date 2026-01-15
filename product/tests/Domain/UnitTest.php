<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Domain;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Product\Unit;

final class UnitTest extends TestCase
{
    public function testCanCreateUnitWithCustomValue(): void
    {
        $unit = Unit::of('kilogram');

        self::assertEquals('kilogram', $unit->value());
        self::assertEquals('kilogram', $unit->asString());
    }

    public function testFactoryMethodPiece(): void
    {
        $unit = Unit::piece();

        self::assertEquals('piece', $unit->value());
    }

    public function testFactoryMethodKilogram(): void
    {
        $unit = Unit::kilogram();

        self::assertEquals('kilogram', $unit->value());
    }

    public function testFactoryMethodGram(): void
    {
        $unit = Unit::gram();

        self::assertEquals('gram', $unit->value());
    }

    public function testFactoryMethodLiter(): void
    {
        $unit = Unit::liter();

        self::assertEquals('liter', $unit->value());
    }

    public function testFactoryMethodMilliliter(): void
    {
        $unit = Unit::milliliter();

        self::assertEquals('milliliter', $unit->value());
    }

    public function testFactoryMethodMeter(): void
    {
        $unit = Unit::meter();

        self::assertEquals('meter', $unit->value());
    }

    public function testFactoryMethodCentimeter(): void
    {
        $unit = Unit::centimeter();

        self::assertEquals('centimeter', $unit->value());
    }

    public function testEqualityComparison(): void
    {
        $unit1 = Unit::of('kilogram');
        $unit2 = Unit::of('kilogram');
        $unit3 = Unit::of('gram');

        self::assertEquals($unit1, $unit2);
        self::assertNotEquals($unit1, $unit3);
    }
}
