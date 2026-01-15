<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Domain\SerialNumber;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Product\SerialNumber\TextualSerialNumber;

final class TextualSerialNumberTest extends TestCase
{
    public function testCanBeCreatedWithValidValue(): void
    {
        $serialNumber = TextualSerialNumber::of('SN-12345-ABC');

        self::assertEquals('SN-12345-ABC', $serialNumber->value());
        self::assertEquals('SN-12345-ABC', $serialNumber->asString());
    }

    public function testRejectsEmptyValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Serial number cannot be empty');

        TextualSerialNumber::of('');
    }

    public function testRejectsWhitespaceOnlyValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Serial number cannot be empty');

        TextualSerialNumber::of('   ');
    }

    public function testTrimsWhitespace(): void
    {
        $serialNumber = TextualSerialNumber::of('  ABC-123  ');

        self::assertEquals('ABC-123', $serialNumber->value());
    }

    public function testProvidesSerialNumberType(): void
    {
        $serialNumber = TextualSerialNumber::of('SN-12345');

        self::assertEquals('TEXTUAL', $serialNumber->type());
    }

    public function testTwoSerialNumbersWithSameValueAreEqual(): void
    {
        $sn1 = TextualSerialNumber::of('ABC-123');
        $sn2 = TextualSerialNumber::of('ABC-123');

        self::assertEquals($sn1, $sn2);
    }
}
