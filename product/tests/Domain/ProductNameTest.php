<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Domain;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Product\ProductName;

final class ProductNameTest extends TestCase
{
    public function testCanBeCreatedWithValidValue(): void
    {
        $name = ProductName::of('MacBook Pro 16"');

        self::assertEquals('MacBook Pro 16"', $name->value());
        self::assertEquals('MacBook Pro 16"', $name->asString());
    }

    public function testRejectsEmptyValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product name cannot be empty');

        ProductName::of('');
    }

    public function testRejectsWhitespaceOnlyValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Product name cannot be empty');

        ProductName::of('   ');
    }

    public function testTrimsWhitespace(): void
    {
        $name = ProductName::of('  iPhone 15  ');

        self::assertEquals('iPhone 15', $name->value());
    }

    public function testTwoNamesWithSameValueAreEqual(): void
    {
        $name1 = ProductName::of('Samsung Galaxy S24');
        $name2 = ProductName::of('Samsung Galaxy S24');

        self::assertEquals($name1, $name2);
    }
}
