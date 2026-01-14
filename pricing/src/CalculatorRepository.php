<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Pricing;

interface CalculatorRepository
{
    public function save(Calculator $calculator): void;

    public function findByName(string $name): ?Calculator;

    /**
     * @return list<Calculator>
     */
    public function findAll(): array;
}
