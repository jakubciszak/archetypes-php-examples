<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Pricing;

use Brick\Math\BigDecimal;
use SoftwareArchetypes\Quantity\Money\Money;

final readonly class SimpleFixedCalculator implements Calculator
{
    private CalculatorId $id;

    public function __construct(
        private string $calculatorName,
        private BigDecimal $amount
    ) {
        $this->id = CalculatorId::generate();
    }

    public function calculate(Parameters $parameters): Money
    {
        return Money::pln($this->amount);
    }

    public function describe(): string
    {
        return $this->getType()->formatDescription($this->amount->__toString());
    }

    public function getType(): CalculatorType
    {
        return CalculatorType::SIMPLE_FIXED;
    }

    public function getId(): CalculatorId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->calculatorName;
    }
}
