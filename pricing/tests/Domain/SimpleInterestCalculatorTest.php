<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Pricing\Tests\Domain;

use Brick\Math\BigDecimal;
use DateInterval;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Pricing\CalculatorType;
use SoftwareArchetypes\Pricing\Parameters;
use SoftwareArchetypes\Pricing\SimpleInterestCalculator;
use SoftwareArchetypes\Quantity\Money\Money;

final class SimpleInterestCalculatorTest extends TestCase
{
    public function testCanCreateSimpleInterestCalculator(): void
    {
        $calculator = new SimpleInterestCalculator('test-calculator', BigDecimal::of('6'));

        $this->assertEquals('test-calculator', $calculator->name());
        $this->assertInstanceOf(SimpleInterestCalculator::class, $calculator);
    }

    public function testGetTypeReturnsSimpleInterest(): void
    {
        $calculator = new SimpleInterestCalculator('test-calculator', BigDecimal::of('6'));

        $this->assertEquals(CalculatorType::SIMPLE_INTEREST, $calculator->getType());
    }

    public function testDescribeIncludesRate(): void
    {
        $calculator = new SimpleInterestCalculator('test-calculator', BigDecimal::of('6'));

        $description = $calculator->describe();

        $this->assertStringContainsString('6', $description);
        $this->assertStringContainsString('%', $description);
    }

    public function testCalculateYearlyInterest(): void
    {
        $calculator = new SimpleInterestCalculator('test-calculator', BigDecimal::of('6'));
        $parameters = new Parameters([
            'base' => Money::pln('1000'),
            'unit' => DateInterval::createFromDateString('1 year'),
        ]);

        $result = $calculator->calculate($parameters);

        $this->assertTrue($result->equals(Money::pln('60.00')));
    }

    public function testCalculateMonthlyInterest(): void
    {
        $calculator = new SimpleInterestCalculator('test-calculator', BigDecimal::of('12'));
        $parameters = new Parameters([
            'base' => Money::pln('1000'),
            'unit' => DateInterval::createFromDateString('1 month'),
        ]);

        $result = $calculator->calculate($parameters);

        $this->assertTrue($result->equals(Money::pln('10.00')));
    }

    public function testCalculateDailyInterest(): void
    {
        $calculator = new SimpleInterestCalculator('test-calculator', BigDecimal::of('3.65'));
        $parameters = new Parameters([
            'base' => Money::pln('365'),
            'unit' => DateInterval::createFromDateString('1 day'),
        ]);

        $result = $calculator->calculate($parameters);

        $expected = Money::pln('0.04');
        $this->assertTrue($result->equals($expected), sprintf(
            'Expected %s but got %s',
            $expected->__toString(),
            $result->__toString()
        ));
    }

    public function testThrowsExceptionWhenBaseParameterMissing(): void
    {
        $calculator = new SimpleInterestCalculator('test-calculator', BigDecimal::of('6'));
        $parameters = new Parameters([
            'unit' => DateInterval::createFromDateString('1 year'),
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('SimpleInterestCalculator');
        $calculator->calculate($parameters);
    }

    public function testThrowsExceptionWhenUnitParameterMissing(): void
    {
        $calculator = new SimpleInterestCalculator('test-calculator', BigDecimal::of('6'));
        $parameters = new Parameters([
            'base' => Money::pln('1000'),
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('SimpleInterestCalculator');
        $calculator->calculate($parameters);
    }

    public function testThrowsExceptionForUnsupportedTimeUnit(): void
    {
        $calculator = new SimpleInterestCalculator('test-calculator', BigDecimal::of('6'));
        $parameters = new Parameters([
            'base' => Money::pln('1000'),
            'unit' => DateInterval::createFromDateString('1 hour'),
        ]);

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Unsupported unit');
        $calculator->calculate($parameters);
    }

    public function testHasUniqueId(): void
    {
        $calculator1 = new SimpleInterestCalculator('calc1', BigDecimal::of('6'));
        $calculator2 = new SimpleInterestCalculator('calc2', BigDecimal::of('6'));

        $this->assertNotEquals($calculator1->getId()->toString(), $calculator2->getId()->toString());
    }
}
