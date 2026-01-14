<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Pricing\Tests\Domain;

use Brick\Math\BigDecimal;
use DateInterval;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Pricing\CalculatorType;
use SoftwareArchetypes\Pricing\Infrastructure\InMemoryCalculatorRepository;
use SoftwareArchetypes\Pricing\Parameters;
use SoftwareArchetypes\Pricing\PricingFacade;
use SoftwareArchetypes\Quantity\Money\Money;

final class PricingFacadeTest extends TestCase
{
    private PricingFacade $facade;

    protected function setUp(): void
    {
        $this->facade = new PricingFacade(new InMemoryCalculatorRepository());
    }

    public function testCanAddSimpleFixedCalculator(): void
    {
        $this->facade->addCalculator(
            'fixed-100',
            CalculatorType::SIMPLE_FIXED,
            new Parameters(['amount' => BigDecimal::of('100')])
        );

        $calculators = $this->facade->availableCalculators();

        $this->assertCount(1, $calculators);
        $this->assertEquals('fixed-100', $calculators[0]->name);
    }

    public function testCanAddSimpleInterestCalculator(): void
    {
        $this->facade->addCalculator(
            'interest-6',
            CalculatorType::SIMPLE_INTEREST,
            new Parameters(['annualRate' => BigDecimal::of('6')])
        );

        $calculators = $this->facade->availableCalculators();

        $this->assertCount(1, $calculators);
        $this->assertEquals('interest-6', $calculators[0]->name);
    }

    public function testThrowsExceptionWhenRequiredParametersMissing(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageMatches('/requires field/i');

        $this->facade->addCalculator(
            'invalid',
            CalculatorType::SIMPLE_FIXED,
            Parameters::empty()
        );
    }

    public function testCanCalculateWithSimpleFixedCalculator(): void
    {
        $this->facade->addCalculator(
            'fixed-50',
            CalculatorType::SIMPLE_FIXED,
            new Parameters(['amount' => BigDecimal::of('50')])
        );

        $result = $this->facade->calculate('fixed-50', Parameters::empty());

        $this->assertTrue($result->equals(Money::pln('50')));
    }

    public function testCanCalculateWithSimpleInterestCalculator(): void
    {
        $this->facade->addCalculator(
            'interest-12',
            CalculatorType::SIMPLE_INTEREST,
            new Parameters(['annualRate' => BigDecimal::of('12')])
        );

        $parameters = new Parameters([
            'base' => Money::pln('1000'),
            'unit' => DateInterval::createFromDateString('1 month'),
        ]);

        $result = $this->facade->calculate('interest-12', $parameters);

        $this->assertTrue($result->equals(Money::pln('10.00')));
    }

    public function testThrowsExceptionForNonExistentCalculator(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('could not find calculator');

        $this->facade->calculate('non-existent', Parameters::empty());
    }

    public function testAvailableCalculatorsReturnsEmptyArrayInitially(): void
    {
        $calculators = $this->facade->availableCalculators();

        $this->assertEmpty($calculators);
    }

    public function testAvailableCalculatorsReturnsAllAdded(): void
    {
        $this->facade->addCalculator(
            'fixed-1',
            CalculatorType::SIMPLE_FIXED,
            new Parameters(['amount' => BigDecimal::of('10')])
        );
        $this->facade->addCalculator(
            'interest-1',
            CalculatorType::SIMPLE_INTEREST,
            new Parameters(['annualRate' => BigDecimal::of('5')])
        );

        $calculators = $this->facade->availableCalculators();

        $this->assertCount(2, $calculators);
    }

    public function testListCalculatorsWithDescriptionsGroupsByType(): void
    {
        $this->facade->addCalculator(
            'fixed-1',
            CalculatorType::SIMPLE_FIXED,
            new Parameters(['amount' => BigDecimal::of('10')])
        );
        $this->facade->addCalculator(
            'fixed-2',
            CalculatorType::SIMPLE_FIXED,
            new Parameters(['amount' => BigDecimal::of('20')])
        );
        $this->facade->addCalculator(
            'interest-1',
            CalculatorType::SIMPLE_INTEREST,
            new Parameters(['annualRate' => BigDecimal::of('5')])
        );

        $grouped = $this->facade->listCalculatorsWithDescriptions();

        $this->assertArrayHasKey('simple-fixed', $grouped);
        $this->assertArrayHasKey('simple-interest', $grouped);
        $this->assertCount(2, $grouped['simple-fixed']);
        $this->assertCount(1, $grouped['simple-interest']);
    }

    public function testAvailableCalculatorTypesReturnsAllTypes(): void
    {
        $types = $this->facade->availableCalculatorTypes();

        $this->assertContains(CalculatorType::SIMPLE_FIXED, $types);
        $this->assertContains(CalculatorType::SIMPLE_INTEREST, $types);
    }
}
