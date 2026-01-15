<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Domain\Identifier;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Product\Identifier\GtinProductIdentifier;

final class GtinProductIdentifierTest extends TestCase
{
    public function testCanBeCreatedWithValidGtin(): void
    {
        $identifier = GtinProductIdentifier::of('01234567890128');

        self::assertEquals('01234567890128', $identifier->value());
        self::assertEquals('01234567890128', $identifier->asString());
    }

    public function testAcceptsGtin8(): void
    {
        $identifier = GtinProductIdentifier::of('12345670');

        self::assertEquals('12345670', $identifier->value());
    }

    public function testAcceptsGtin12(): void
    {
        $identifier = GtinProductIdentifier::of('012345678905');

        self::assertEquals('012345678905', $identifier->value());
    }

    public function testAcceptsGtin13(): void
    {
        $identifier = GtinProductIdentifier::of('0123456789012');

        self::assertEquals('0123456789012', $identifier->value());
    }

    public function testAcceptsGtin14(): void
    {
        $identifier = GtinProductIdentifier::of('01234567890128');

        self::assertEquals('01234567890128', $identifier->value());
    }

    public function testRejectsInvalidLength(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('GTIN must be 8, 12, 13, or 14 digits');

        GtinProductIdentifier::of('123456');
    }

    public function testRejectsNonNumeric(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('GTIN must contain only digits');

        GtinProductIdentifier::of('0123456789ABC');
    }

    public function testRejectsEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('GTIN must be 8, 12, 13, or 14 digits');

        GtinProductIdentifier::of('');
    }

    public function testProvidesIdentifierType(): void
    {
        $identifier = GtinProductIdentifier::of('01234567890128');

        self::assertEquals('GTIN', $identifier->type());
    }

    public function testTwoIdentifiersWithSameGtinAreEqual(): void
    {
        $id1 = GtinProductIdentifier::of('01234567890128');
        $id2 = GtinProductIdentifier::of('01234567890128');

        self::assertEquals($id1, $id2);
    }
}
