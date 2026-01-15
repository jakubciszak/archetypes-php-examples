<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Domain;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Product\FeatureValueType;

final class FeatureValueTypeTest extends TestCase
{
    public function testTextType(): void
    {
        $type = FeatureValueType::TEXT;

        self::assertEquals('TEXT', $type->name);
        self::assertTrue($type->isInstance('hello'));
        self::assertFalse($type->isInstance(123));
    }

    public function testIntegerType(): void
    {
        $type = FeatureValueType::INTEGER;

        self::assertEquals('INTEGER', $type->name);
        self::assertTrue($type->isInstance(42));
        self::assertFalse($type->isInstance('42'));
    }

    public function testDecimalType(): void
    {
        $type = FeatureValueType::DECIMAL;

        self::assertEquals('DECIMAL', $type->name);
        self::assertTrue($type->isInstance(\Brick\Math\BigDecimal::of('3.14')));
        self::assertFalse($type->isInstance(3.14));
    }

    public function testDateType(): void
    {
        $type = FeatureValueType::DATE;

        self::assertEquals('DATE', $type->name);
        self::assertTrue($type->isInstance(new \DateTimeImmutable()));
        self::assertFalse($type->isInstance('2025-01-15'));
    }

    public function testBooleanType(): void
    {
        $type = FeatureValueType::BOOLEAN;

        self::assertEquals('BOOLEAN', $type->name);
        self::assertTrue($type->isInstance(true));
        self::assertTrue($type->isInstance(false));
        self::assertFalse($type->isInstance(1));
    }

    public function testCastFromStringForText(): void
    {
        $type = FeatureValueType::TEXT;
        $value = $type->castFromString('hello world');

        self::assertIsString($value);
        self::assertEquals('hello world', $value);
    }

    public function testCastFromStringForInteger(): void
    {
        $type = FeatureValueType::INTEGER;
        $value = $type->castFromString('42');

        self::assertIsInt($value);
        self::assertEquals(42, $value);
    }

    public function testCastFromStringForDecimal(): void
    {
        $type = FeatureValueType::DECIMAL;
        $value = $type->castFromString('3.14159');

        self::assertInstanceOf(\Brick\Math\BigDecimal::class, $value);
        self::assertEquals('3.14159', (string) $value);
    }

    public function testCastFromStringForDate(): void
    {
        $type = FeatureValueType::DATE;
        $value = $type->castFromString('2025-01-15');

        self::assertInstanceOf(\DateTimeImmutable::class, $value);
        self::assertEquals('2025-01-15', $value->format('Y-m-d'));
    }

    public function testCastFromStringForBoolean(): void
    {
        $type = FeatureValueType::BOOLEAN;

        self::assertTrue($type->castFromString('true'));
        self::assertTrue($type->castFromString('1'));
        self::assertFalse($type->castFromString('false'));
        self::assertFalse($type->castFromString('0'));
    }

    public function testCastToStringForText(): void
    {
        $type = FeatureValueType::TEXT;
        $result = $type->castToString('hello world');

        self::assertEquals('hello world', $result);
    }

    public function testCastToStringForInteger(): void
    {
        $type = FeatureValueType::INTEGER;
        $result = $type->castToString(42);

        self::assertEquals('42', $result);
    }

    public function testCastToStringForDecimal(): void
    {
        $type = FeatureValueType::DECIMAL;
        $result = $type->castToString(\Brick\Math\BigDecimal::of('3.14'));

        self::assertEquals('3.14', $result);
    }

    public function testCastToStringForDate(): void
    {
        $type = FeatureValueType::DATE;
        $date = new \DateTimeImmutable('2025-01-15');
        $result = $type->castToString($date);

        self::assertEquals('2025-01-15', $result);
    }

    public function testCastToStringForBoolean(): void
    {
        $type = FeatureValueType::BOOLEAN;

        self::assertEquals('true', $type->castToString(true));
        self::assertEquals('false', $type->castToString(false));
    }
}
