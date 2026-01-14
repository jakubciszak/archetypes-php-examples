<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Pricing\Tests\Domain;

use Brick\Math\BigDecimal;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Pricing\CalculatorType;
use SoftwareArchetypes\Pricing\Parameters;
use SoftwareArchetypes\Pricing\SimpleFixedCalculator;
use SoftwareArchetypes\Quantity\Money\Money;

final class SimpleFixedCalculatorTest extends TestCase
{
    public function testCanCreateSimpleFixedCalculator(): void
    {
        $calculator = new SimpleFixedCalculator('test-calculator', BigDecimal::of('20'));

        $this->assertEquals('test-calculator', $calculator->name());
        $this->assertInstanceOf(SimpleFixedCalculator::class, $calculator);
    }

    public function testReturnsFixedAmount(): void
    {
        $calculator = new SimpleFixedCalculator('test-calculator', BigDecimal::of('100'));

        $result = $calculator->calculate(Parameters::empty());

        $this->assertTrue($result->equals(Money::pln('100')));
    }

    public function testReturnsFixedAmountRegardlessOfParameters(): void
    {
        $calculator = new SimpleFixedCalculator('test-calculator', BigDecimal::of('50'));
        $parameters = new Parameters(['someKey' => 'someValue', 'anotherKey' => 123]);

        $result = $calculator->calculate($parameters);

        $this->assertTrue($result->equals(Money::pln('50')));
    }

    public function testGetTypeReturnsSimpleFixed(): void
    {
        $calculator = new SimpleFixedCalculator('test-calculator', BigDecimal::of('20'));

        $this->assertEquals(CalculatorType::SIMPLE_FIXED, $calculator->getType());
    }

    public function testDescribeIncludesAmount(): void
    {
        $calculator = new SimpleFixedCalculator('test-calculator', BigDecimal::of('20'));

        $description = $calculator->describe();

        $this->assertStringContainsString('20', $description);
        $this->assertStringContainsString('PLN', $description);
    }

    public function testHasUniqueId(): void
    {
        $calculator1 = new SimpleFixedCalculator('calc1', BigDecimal::of('20'));
        $calculator2 = new SimpleFixedCalculator('calc2', BigDecimal::of('20'));

        $this->assertNotEquals($calculator1->getId()->toString(), $calculator2->getId()->toString());
    }

    public function testWorksWithDecimalAmounts(): void
    {
        $calculator = new SimpleFixedCalculator('test-calculator', BigDecimal::of('19.99'));

        $result = $calculator->calculate(Parameters::empty());

        $this->assertTrue($result->equals(Money::pln('19.99')));
    }
}
