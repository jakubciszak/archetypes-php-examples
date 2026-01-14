<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Pricing;

use SoftwareArchetypes\Quantity\Money\Money;

interface Calculator
{
    public function calculate(Parameters $parameters): Money;

    public function describe(): string;

    public function getType(): CalculatorType;

    public function getId(): CalculatorId;

    public function name(): string;
}
