<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Pricing\Tests\Domain;

use Brick\Math\BigDecimal;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Pricing\CalculatorType;
use SoftwareArchetypes\Pricing\CalculatorView;
use SoftwareArchetypes\Pricing\SimpleFixedCalculator;
use SoftwareArchetypes\Pricing\SimpleInterestCalculator;

final class CalculatorViewTest extends TestCase
{
    public function testCanCreateFromSimpleFixedCalculator(): void
    {
        $calculator = new SimpleFixedCalculator('test-calc', BigDecimal::of('20'));

        $view = CalculatorView::from($calculator);

        $this->assertInstanceOf(CalculatorView::class, $view);
        $this->assertEquals($calculator->getId(), $view->calculatorId);
        $this->assertEquals('test-calc', $view->name);
        $this->assertEquals(CalculatorType::SIMPLE_FIXED, $view->type);
        $this->assertStringContainsString('20', $view->description);
    }

    public function testCanCreateFromSimpleInterestCalculator(): void
    {
        $calculator = new SimpleInterestCalculator('interest-calc', BigDecimal::of('6'));

        $view = CalculatorView::from($calculator);

        $this->assertInstanceOf(CalculatorView::class, $view);
        $this->assertEquals($calculator->getId(), $view->calculatorId);
        $this->assertEquals('interest-calc', $view->name);
        $this->assertEquals(CalculatorType::SIMPLE_INTEREST, $view->type);
        $this->assertStringContainsString('6', $view->description);
    }

    public function testViewContainsAllCalculatorInformation(): void
    {
        $calculator = new SimpleFixedCalculator('full-test', BigDecimal::of('99.99'));

        $view = CalculatorView::from($calculator);

        $this->assertEquals($calculator->getId(), $view->calculatorId);
        $this->assertEquals($calculator->name(), $view->name);
        $this->assertEquals($calculator->getType(), $view->type);
        $this->assertEquals($calculator->describe(), $view->description);
    }
}
