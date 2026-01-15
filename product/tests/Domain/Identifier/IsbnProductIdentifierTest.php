<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Domain\Identifier;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Product\Identifier\IsbnProductIdentifier;

final class IsbnProductIdentifierTest extends TestCase
{
    public function testCanBeCreatedWithValidIsbn10(): void
    {
        $identifier = IsbnProductIdentifier::of('0-306-40615-2');

        self::assertEquals('0-306-40615-2', $identifier->value());
        self::assertEquals('0-306-40615-2', $identifier->asString());
    }

    public function testCanBeCreatedWithValidIsbn13(): void
    {
        $identifier = IsbnProductIdentifier::of('978-0-306-40615-7');

        self::assertEquals('978-0-306-40615-7', $identifier->value());
    }

    public function testAcceptsIsbn10WithoutHyphens(): void
    {
        $identifier = IsbnProductIdentifier::of('0306406152');

        self::assertEquals('0306406152', $identifier->value());
    }

    public function testAcceptsIsbn13WithoutHyphens(): void
    {
        $identifier = IsbnProductIdentifier::of('9780306406157');

        self::assertEquals('9780306406157', $identifier->value());
    }

    public function testRejectsInvalidLength(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ISBN must be 10 or 13 characters');

        IsbnProductIdentifier::of('123456');
    }

    public function testRejectsInvalidCharacters(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ISBN contains invalid characters');

        IsbnProductIdentifier::of('ABC-DEF-GHI-J');
    }

    public function testRejectsEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('ISBN must be 10 or 13 characters');

        IsbnProductIdentifier::of('');
    }

    public function testProvidesIdentifierType(): void
    {
        $identifier = IsbnProductIdentifier::of('0-306-40615-2');

        self::assertEquals('ISBN', $identifier->type());
    }

    public function testTwoIdentifiersWithSameIsbnAreEqual(): void
    {
        $id1 = IsbnProductIdentifier::of('0-306-40615-2');
        $id2 = IsbnProductIdentifier::of('0-306-40615-2');

        self::assertEquals($id1, $id2);
    }

    public function testAcceptsIsbn10WithXCheckDigit(): void
    {
        $identifier = IsbnProductIdentifier::of('043942089X');

        self::assertEquals('043942089X', $identifier->value());
    }
}
