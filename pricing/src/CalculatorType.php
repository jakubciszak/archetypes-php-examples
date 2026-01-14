<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Pricing;

enum CalculatorType
{
    case SIMPLE_FIXED;
    case SIMPLE_INTEREST;

    public function getTypeName(): string
    {
        return match ($this) {
            self::SIMPLE_FIXED => 'simple-fixed',
            self::SIMPLE_INTEREST => 'simple-interest',
        };
    }

    public function formatDescription(string $value): string
    {
        return match ($this) {
            self::SIMPLE_FIXED => sprintf('Fixed amount calculator - returns %s PLN regardless', $value),
            self::SIMPLE_INTEREST => sprintf(
                'Annual interest calculator - calculates %s%% annual interest based on base and time unit',
                $value
            ),
        };
    }

    /**
     * @return array<string>
     */
    public function requiredCreationFields(): array
    {
        return match ($this) {
            self::SIMPLE_FIXED => ['amount'],
            self::SIMPLE_INTEREST => ['annualRate'],
        };
    }

    /**
     * @return array<string>
     */
    public function requiredCalculationFields(): array
    {
        return match ($this) {
            self::SIMPLE_FIXED => [],
            self::SIMPLE_INTEREST => ['base', 'unit'],
        };
    }
}
