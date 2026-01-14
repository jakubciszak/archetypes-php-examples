<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Pricing\Infrastructure;

use SoftwareArchetypes\Pricing\Calculator;
use SoftwareArchetypes\Pricing\CalculatorRepository;

final class InMemoryCalculatorRepository implements CalculatorRepository
{
    /**
     * @var array<string, Calculator>
     */
    private array $calculators = [];

    public function save(Calculator $calculator): void
    {
        $this->calculators[$calculator->name()] = $calculator;
    }

    public function findByName(string $name): ?Calculator
    {
        return $this->calculators[$name] ?? null;
    }

    /**
     * @return list<Calculator>
     */
    public function findAll(): array
    {
        return array_values($this->calculators);
    }
}
