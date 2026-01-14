<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Pricing\Tests\Integration;

use Brick\Math\BigDecimal;
use DateInterval;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Pricing\CalculatorType;
use SoftwareArchetypes\Pricing\Infrastructure\InMemoryCalculatorRepository;
use SoftwareArchetypes\Pricing\Parameters;
use SoftwareArchetypes\Pricing\PricingFacade;
use SoftwareArchetypes\Quantity\Money\Money;

final class PricingIntegrationTest extends TestCase
{
    private PricingFacade $facade;

    protected function setUp(): void
    {
        $this->facade = new PricingFacade(new InMemoryCalculatorRepository());
    }

    public function testCompleteFixedPricingFlow(): void
    {
        $this->facade->addCalculator(
            'monthly-fee',
            CalculatorType::SIMPLE_FIXED,
            new Parameters(['amount' => BigDecimal::of('29.99')])
        );

        $result = $this->facade->calculate('monthly-fee', Parameters::empty());

        $this->assertTrue($result->equals(Money::pln('29.99')));
        $this->assertEquals('PLN', $result->currency());
    }

    public function testCompleteInterestCalculationFlow(): void
    {
        $this->facade->addCalculator(
            'loan-interest',
            CalculatorType::SIMPLE_INTEREST,
            new Parameters(['annualRate' => BigDecimal::of('5')])
        );

        $loanParameters = new Parameters([
            'base' => Money::pln('10000'),
            'unit' => DateInterval::createFromDateString('1 year'),
        ]);

        $yearlyInterest = $this->facade->calculate('loan-interest', $loanParameters);

        $this->assertTrue($yearlyInterest->equals(Money::pln('500.00')));
    }

    public function testMultipleCalculatorsWorkingTogether(): void
    {
        $this->facade->addCalculator(
            'setup-fee',
            CalculatorType::SIMPLE_FIXED,
            new Parameters(['amount' => BigDecimal::of('199.00')])
        );

        $this->facade->addCalculator(
            'monthly-interest',
            CalculatorType::SIMPLE_INTEREST,
            new Parameters(['annualRate' => BigDecimal::of('12')])
        );

        $setupFee = $this->facade->calculate('setup-fee', Parameters::empty());
        $monthlyInterest = $this->facade->calculate('monthly-interest', new Parameters([
            'base' => Money::pln('5000'),
            'unit' => DateInterval::createFromDateString('1 month'),
        ]));

        $this->assertTrue($setupFee->equals(Money::pln('199.00')));
        $this->assertTrue($monthlyInterest->equals(Money::pln('50.00')));

        $totalCost = $setupFee->add($monthlyInterest);
        $this->assertTrue($totalCost->equals(Money::pln('249.00')));
    }

    public function testListingCalculatorsByType(): void
    {
        $this->facade->addCalculator(
            'standard-fee',
            CalculatorType::SIMPLE_FIXED,
            new Parameters(['amount' => BigDecimal::of('99')])
        );

        $this->facade->addCalculator(
            'premium-fee',
            CalculatorType::SIMPLE_FIXED,
            new Parameters(['amount' => BigDecimal::of('199')])
        );

        $this->facade->addCalculator(
            'credit-interest',
            CalculatorType::SIMPLE_INTEREST,
            new Parameters(['annualRate' => BigDecimal::of('7.5')])
        );

        $grouped = $this->facade->listCalculatorsWithDescriptions();

        $this->assertCount(2, $grouped);
        $this->assertCount(2, $grouped['simple-fixed']);
        $this->assertCount(1, $grouped['simple-interest']);
    }

    public function testCalculatorDescriptionsAreHelpful(): void
    {
        $this->facade->addCalculator(
            'service-fee',
            CalculatorType::SIMPLE_FIXED,
            new Parameters(['amount' => BigDecimal::of('50')])
        );

        $calculators = $this->facade->availableCalculators();

        $this->assertCount(1, $calculators);
        $this->assertStringContainsString('50', $calculators[0]->description);
        $this->assertStringContainsString('PLN', $calculators[0]->description);
    }

    public function testDailyInterestCalculation(): void
    {
        $this->facade->addCalculator(
            'daily-rate',
            CalculatorType::SIMPLE_INTEREST,
            new Parameters(['annualRate' => BigDecimal::of('10')])
        );

        $dailyInterest = $this->facade->calculate('daily-rate', new Parameters([
            'base' => Money::pln('1000'),
            'unit' => DateInterval::createFromDateString('1 day'),
        ]));

        $expectedDaily = Money::pln('0.27');
        $this->assertTrue(
            $dailyInterest->equals($expectedDaily),
            sprintf('Expected %s but got %s', $expectedDaily, $dailyInterest)
        );
    }

    public function testWeeklyInterestCalculation(): void
    {
        $this->facade->addCalculator(
            'weekly-rate',
            CalculatorType::SIMPLE_INTEREST,
            new Parameters(['annualRate' => BigDecimal::of('52')])
        );

        $weeklyInterest = $this->facade->calculate('weekly-rate', new Parameters([
            'base' => Money::pln('1000'),
            'unit' => DateInterval::createFromDateString('7 days'),
        ]));

        $expectedWeekly = Money::pln('10.00');
        $this->assertTrue(
            $weeklyInterest->equals($expectedWeekly),
            sprintf('Expected %s but got %s', $expectedWeekly, $weeklyInterest)
        );
    }
}
