<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Domain\Constraint;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Product\Constraint\RegexConstraint;
use SoftwareArchetypes\Product\FeatureValueType;

final class RegexConstraintTest extends TestCase
{
    public function testAcceptsValueMatchingPattern(): void
    {
        $constraint = new RegexConstraint('/^[A-Z]{3}-\d{4}$/');

        self::assertTrue($constraint->isSatisfiedBy('ABC-1234'));
        self::assertTrue($constraint->isSatisfiedBy('XYZ-9999'));
    }

    public function testRejectsValueNotMatchingPattern(): void
    {
        $constraint = new RegexConstraint('/^[A-Z]{3}-\d{4}$/');

        self::assertFalse($constraint->isSatisfiedBy('abc-1234'));
        self::assertFalse($constraint->isSatisfiedBy('AB-1234'));
        self::assertFalse($constraint->isSatisfiedBy('ABC-123'));
        self::assertFalse($constraint->isSatisfiedBy('ABC1234'));
    }

    public function testRejectsNonStringValue(): void
    {
        $constraint = new RegexConstraint('/^\d+$/');

        self::assertFalse($constraint->isSatisfiedBy(123));
        self::assertFalse($constraint->isSatisfiedBy(true));
    }

    public function testWorksWithEmailPattern(): void
    {
        $constraint = new RegexConstraint('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/');

        self::assertTrue($constraint->isSatisfiedBy('user@example.com'));
        self::assertTrue($constraint->isSatisfiedBy('test.user+tag@domain.co.uk'));
        self::assertFalse($constraint->isSatisfiedBy('invalid.email'));
        self::assertFalse($constraint->isSatisfiedBy('@example.com'));
    }

    public function testWorksWithPhonePattern(): void
    {
        $constraint = new RegexConstraint('/^\+?[1-9]\d{1,14}$/');

        self::assertTrue($constraint->isSatisfiedBy('+1234567890'));
        self::assertTrue($constraint->isSatisfiedBy('1234567890'));
        self::assertFalse($constraint->isSatisfiedBy('+0123456789'));
        self::assertFalse($constraint->isSatisfiedBy('123-456-7890'));
    }

    public function testWorksWithHexColorPattern(): void
    {
        $constraint = new RegexConstraint('/^#[0-9A-Fa-f]{6}$/');

        self::assertTrue($constraint->isSatisfiedBy('#FF5733'));
        self::assertTrue($constraint->isSatisfiedBy('#000000'));
        self::assertTrue($constraint->isSatisfiedBy('#ffffff'));
        self::assertFalse($constraint->isSatisfiedBy('#FFF'));
        self::assertFalse($constraint->isSatisfiedBy('FF5733'));
    }

    public function testProvidesValueType(): void
    {
        $constraint = new RegexConstraint('/^test$/');

        self::assertEquals(FeatureValueType::TEXT, $constraint->valueType());
    }

    public function testProvidesPattern(): void
    {
        $pattern = '/^[A-Z]+$/';
        $constraint = new RegexConstraint($pattern);

        self::assertEquals($pattern, $constraint->pattern());
    }

    public function testRejectsInvalidRegexPattern(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Invalid regular expression pattern');

        new RegexConstraint('[invalid');
    }

    public function testRejectsEmptyPattern(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Pattern cannot be empty');

        new RegexConstraint('');
    }

    public function testWorksWithCaseInsensitivePattern(): void
    {
        $constraint = new RegexConstraint('/^[a-z]+$/i');

        self::assertTrue($constraint->isSatisfiedBy('abc'));
        self::assertTrue($constraint->isSatisfiedBy('ABC'));
        self::assertTrue($constraint->isSatisfiedBy('AbC'));
    }
}
