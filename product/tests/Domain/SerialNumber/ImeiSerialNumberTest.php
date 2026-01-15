<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Domain\SerialNumber;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Product\SerialNumber\ImeiSerialNumber;

final class ImeiSerialNumberTest extends TestCase
{
    public function testCanBeCreatedWithValidImei(): void
    {
        $serialNumber = ImeiSerialNumber::of('123456789012345');

        self::assertEquals('123456789012345', $serialNumber->value());
        self::assertEquals('123456789012345', $serialNumber->asString());
    }

    public function testRejectsInvalidLength(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('IMEI must be exactly 15 digits');

        ImeiSerialNumber::of('12345');
    }

    public function testRejectsNonNumericValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('IMEI must contain only digits');

        ImeiSerialNumber::of('12345678901234A');
    }

    public function testRejectsEmptyValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('IMEI must be exactly 15 digits');

        ImeiSerialNumber::of('');
    }

    public function testProvidesSerialNumberType(): void
    {
        $serialNumber = ImeiSerialNumber::of('123456789012345');

        self::assertEquals('IMEI', $serialNumber->type());
    }

    public function testTwoImeiWithSameValueAreEqual(): void
    {
        $sn1 = ImeiSerialNumber::of('123456789012345');
        $sn2 = ImeiSerialNumber::of('123456789012345');

        self::assertEquals($sn1, $sn2);
    }
}
