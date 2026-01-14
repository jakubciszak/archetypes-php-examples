<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Pricing;

use Brick\Math\BigDecimal;
use Brick\Math\RoundingMode;
use DateInterval;
use InvalidArgumentException;
use SoftwareArchetypes\Quantity\Money\Money;

final readonly class SimpleInterestCalculator implements Calculator
{
    private const int SCALE = 10;

    private CalculatorId $id;

    public function __construct(
        private string $calculatorName,
        private BigDecimal $annualRate
    ) {
        $this->id = CalculatorId::generate();
    }

    public function calculate(Parameters $parameters): Money
    {
        if (!$parameters->containsAll($this->getType()->requiredCalculationFields())) {
            throw new InvalidArgumentException(sprintf(
                'SimpleInterestCalculator requires fields %s',
                implode(', ', $this->getType()->requiredCalculationFields())
            ));
        }

        $base = $parameters->get('base');
        if (!$base instanceof Money) {
            throw new InvalidArgumentException('Parameter "base" must be an instance of Money');
        }

        $unit = $parameters->get('unit');
        if (!$unit instanceof DateInterval) {
            throw new InvalidArgumentException('Parameter "unit" must be an instance of DateInterval');
        }

        $rate = $this->annualRate->dividedBy(BigDecimal::of('100'), self::SCALE, RoundingMode::HALF_UP);
        $unitRate = $rate->dividedBy($this->unitsPerYear($unit), self::SCALE, RoundingMode::HALF_UP);
        $baseAmount = $base->value();

        $result = $baseAmount->multipliedBy($unitRate);
        $rounded = $result->toScale(2, RoundingMode::HALF_UP);

        return Money::pln($rounded);
    }

    public function describe(): string
    {
        return $this->getType()->formatDescription($this->annualRate->__toString());
    }

    public function getType(): CalculatorType
    {
        return CalculatorType::SIMPLE_INTEREST;
    }

    public function getId(): CalculatorId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->calculatorName;
    }

    private function unitsPerYear(DateInterval $unit): BigDecimal
    {
        if ($unit->y >= 1) {
            return BigDecimal::of('1');
        }

        if ($unit->m >= 1) {
            return BigDecimal::of('12');
        }

        if ($unit->d >= 7) {
            return BigDecimal::of('52');
        }

        if ($unit->d >= 1) {
            return BigDecimal::of('365');
        }

        throw new InvalidArgumentException(sprintf(
            'Unsupported unit for annual calculation: %s',
            $unit->format('%y years %m months %d days')
        ));
    }
}
