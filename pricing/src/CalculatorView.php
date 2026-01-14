<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Pricing;

final readonly class CalculatorView
{
    public function __construct(
        public CalculatorId $calculatorId,
        public string $name,
        public CalculatorType $type,
        public string $description
    ) {
    }

    public static function from(Calculator $calculator): self
    {
        return new self(
            $calculator->getId(),
            $calculator->name(),
            $calculator->getType(),
            $calculator->describe()
        );
    }
}
