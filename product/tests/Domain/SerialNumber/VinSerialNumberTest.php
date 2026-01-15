<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Domain\SerialNumber;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Product\SerialNumber\VinSerialNumber;

final class VinSerialNumberTest extends TestCase
{
    public function testCanBeCreatedWithValidVin(): void
    {
        $serialNumber = VinSerialNumber::of('1HGBH41JXMN109186');

        self::assertEquals('1HGBH41JXMN109186', $serialNumber->value());
        self::assertEquals('1HGBH41JXMN109186', $serialNumber->asString());
    }

    public function testRejectsInvalidLength(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('VIN must be exactly 17 characters');

        VinSerialNumber::of('1HGBH41JXMN');
    }

    public function testRejectsInvalidCharacters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('VIN contains invalid characters');

        VinSerialNumber::of('1HGBH41JXMN10918O'); // Contains 'O' which is not allowed
    }

    public function testRejectsEmptyValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('VIN must be exactly 17 characters');

        VinSerialNumber::of('');
    }

    public function testAcceptsUpperCaseOnly(): void
    {
        $serialNumber = VinSerialNumber::of('1HGBH41JXMN109186');

        self::assertEquals('1HGBH41JXMN109186', $serialNumber->value());
    }

    public function testConvertsLowerCaseToUpperCase(): void
    {
        $serialNumber = VinSerialNumber::of('1hgbh41jxmn109186');

        self::assertEquals('1HGBH41JXMN109186', $serialNumber->value());
    }

    public function testProvidesSerialNumberType(): void
    {
        $serialNumber = VinSerialNumber::of('1HGBH41JXMN109186');

        self::assertEquals('VIN', $serialNumber->type());
    }

    public function testTwoVinsWithSameValueAreEqual(): void
    {
        $sn1 = VinSerialNumber::of('1HGBH41JXMN109186');
        $sn2 = VinSerialNumber::of('1HGBH41JXMN109186');

        self::assertEquals($sn1, $sn2);
    }
}
