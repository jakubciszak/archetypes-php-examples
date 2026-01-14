<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Pricing;

use InvalidArgumentException;
use SoftwareArchetypes\Quantity\Money\Money;

final class PricingFacade
{
    public function __construct(
        private readonly CalculatorRepository $repository
    ) {
    }

    /**
     * @return list<CalculatorView>
     */
    public function availableCalculators(): array
    {
        return array_map(
            fn (Calculator $calculator) => CalculatorView::from($calculator),
            $this->repository->findAll()
        );
    }

    public function addCalculator(string $name, CalculatorType $type, Parameters $parameters): void
    {
        $calculator = $this->createCalculator($name, $type, $parameters);
        $this->repository->save($calculator);
    }

    public function calculate(string $calculatorName, Parameters $parameters): Money
    {
        $calculator = $this->repository->findByName($calculatorName);

        if ($calculator === null) {
            throw new InvalidArgumentException(sprintf('could not find calculator %s', $calculatorName));
        }

        return $calculator->calculate($parameters);
    }

    /**
     * @return array<string, list<CalculatorView>>
     */
    public function listCalculatorsWithDescriptions(): array
    {
        $calculators = $this->repository->findAll();
        $grouped = [];

        foreach ($calculators as $calculator) {
            $type = $calculator->getType();
            $typeName = $type->getTypeName();
            if (!isset($grouped[$typeName])) {
                $grouped[$typeName] = [];
            }
            $grouped[$typeName][] = CalculatorView::from($calculator);
        }

        return $grouped;
    }

    /**
     * @return list<CalculatorType>
     */
    public function availableCalculatorTypes(): array
    {
        return CalculatorType::cases();
    }

    private function createCalculator(string $name, CalculatorType $type, Parameters $parameters): Calculator
    {
        $requiredFields = $type->requiredCreationFields();
        if (!$parameters->containsAll($requiredFields)) {
            throw new InvalidArgumentException(sprintf(
                'Calculator %s requires field %s, but passed only %s',
                $type->getTypeName(),
                implode(', ', $requiredFields),
                implode(', ', $parameters->keys())
            ));
        }

        return match ($type) {
            CalculatorType::SIMPLE_FIXED => new SimpleFixedCalculator(
                $name,
                $parameters->getBigDecimal('amount')
            ),
            CalculatorType::SIMPLE_INTEREST => new SimpleInterestCalculator(
                $name,
                $parameters->getBigDecimal('annualRate')
            ),
        };
    }
}
