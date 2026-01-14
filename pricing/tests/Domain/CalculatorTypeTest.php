<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Pricing\Tests\Domain;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Pricing\CalculatorType;

final class CalculatorTypeTest extends TestCase
{
    public function testSimpleFixedTypeExists(): void
    {
        $type = CalculatorType::SIMPLE_FIXED;

        $this->assertEquals('simple-fixed', $type->getTypeName());
    }

    public function testSimpleInterestTypeExists(): void
    {
        $type = CalculatorType::SIMPLE_INTEREST;

        $this->assertEquals('simple-interest', $type->getTypeName());
    }

    public function testSimpleFixedHasCorrectRequiredCreationFields(): void
    {
        $type = CalculatorType::SIMPLE_FIXED;
        $fields = $type->requiredCreationFields();

        $this->assertContains('amount', $fields);
        $this->assertCount(1, $fields);
    }

    public function testSimpleInterestHasCorrectRequiredCreationFields(): void
    {
        $type = CalculatorType::SIMPLE_INTEREST;
        $fields = $type->requiredCreationFields();

        $this->assertContains('annualRate', $fields);
        $this->assertCount(1, $fields);
    }

    public function testSimpleFixedHasNoRequiredCalculationFields(): void
    {
        $type = CalculatorType::SIMPLE_FIXED;
        $fields = $type->requiredCalculationFields();

        $this->assertEmpty($fields);
    }

    public function testSimpleInterestHasCorrectRequiredCalculationFields(): void
    {
        $type = CalculatorType::SIMPLE_INTEREST;
        $fields = $type->requiredCalculationFields();

        $this->assertContains('base', $fields);
        $this->assertContains('unit', $fields);
        $this->assertCount(2, $fields);
    }

    public function testFormatDescriptionForSimpleFixed(): void
    {
        $type = CalculatorType::SIMPLE_FIXED;
        $description = $type->formatDescription('100');

        $this->assertStringContainsString('100', $description);
        $this->assertStringContainsString('PLN', $description);
    }

    public function testFormatDescriptionForSimpleInterest(): void
    {
        $type = CalculatorType::SIMPLE_INTEREST;
        $description = $type->formatDescription('6');

        $this->assertStringContainsString('6', $description);
        $this->assertStringContainsString('%', $description);
    }
}
