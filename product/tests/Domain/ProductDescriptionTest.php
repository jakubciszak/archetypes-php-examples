<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Domain;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Product\ProductDescription;

final class ProductDescriptionTest extends TestCase
{
    public function testCanBeCreatedWithValidValue(): void
    {
        $description = ProductDescription::of('Professional laptop with M3 chip and Retina display');

        self::assertEquals('Professional laptop with M3 chip and Retina display', $description->value());
        self::assertEquals('Professional laptop with M3 chip and Retina display', $description->asString());
    }

    public function testCanBeCreatedWithEmptyValue(): void
    {
        $description = ProductDescription::of('');

        self::assertEquals('', $description->value());
    }

    public function testTrimsWhitespace(): void
    {
        $description = ProductDescription::of('  A great product  ');

        self::assertEquals('A great product', $description->value());
    }

    public function testTwoDescriptionsWithSameValueAreEqual(): void
    {
        $desc1 = ProductDescription::of('Flagship smartphone');
        $desc2 = ProductDescription::of('Flagship smartphone');

        self::assertEquals($desc1, $desc2);
    }

    public function testSupportsLongText(): void
    {
        $longText = str_repeat('Lorem ipsum dolor sit amet. ', 100);
        $description = ProductDescription::of($longText);

        // Trim expected value since ProductDescription trims input
        self::assertEquals(trim($longText), $description->value());
    }
}
