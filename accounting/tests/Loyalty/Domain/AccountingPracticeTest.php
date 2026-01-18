<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Tests\Loyalty\Domain;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Accounting\Loyalty\Domain\AccountingPractice;
use SoftwareArchetypes\Accounting\Loyalty\Domain\MarketId;
use SoftwareArchetypes\Accounting\Money;

/**
 * Chicago School TDD: Test AccountingPractice with real Money objects.
 */
final class AccountingPracticeTest extends TestCase
{
    public function testCreatesAccountingPracticeForMarket(): void
    {
        $practice = AccountingPractice::forMarket(
            MarketId::fromString('PL'),
            'Poland',
            10,  // points per currency unit
            14,  // maturation period days
            365, // expiration days
            true // round down
        );

        self::assertTrue($practice->marketId()->equals(MarketId::fromString('PL')));
        self::assertSame('Poland', $practice->marketName());
        self::assertSame(10, $practice->pointsPerCurrencyUnit());
        self::assertSame(14, $practice->maturationPeriodDays());
        self::assertSame(365, $practice->pointsExpirationDays());
        self::assertTrue($practice->roundDown());
    }

    public function testCalculatesPointsForPurchase(): void
    {
        $practice = AccountingPractice::forMarket(
            MarketId::fromString('PL'),
            'Poland',
            10,  // 10 points per 1 PLN
            14
        );

        // 5000 cents = 50 PLN = 500 points
        $points = $practice->calculatePoints(Money::of(5000));

        self::assertSame(500, $points->amount());
    }

    public function testCalculatesPointsWithDifferentConversionRates(): void
    {
        $practicePL = AccountingPractice::forMarket(
            MarketId::fromString('PL'),
            'Poland',
            10, // 10 points per PLN
            14
        );

        $practiceDE = AccountingPractice::forMarket(
            MarketId::fromString('DE'),
            'Germany',
            15, // 15 points per EUR
            30
        );

        $amount = Money::of(10000); // 100 currency units

        self::assertSame(1000, $practicePL->calculatePoints($amount)->amount());
        self::assertSame(1500, $practiceDE->calculatePoints($amount)->amount());
    }

    public function testAppliesPromotionalMultiplier(): void
    {
        $practice = AccountingPractice::forMarket(
            MarketId::fromString('PL'),
            'Poland',
            10,
            14,
            365,
            true,
            ['JACKET-001' => 2.0] // 2x points for jackets
        );

        // Regular product: 100 PLN = 1000 points
        $regularPoints = $practice->calculatePoints(Money::of(10000), 'SHIRT-001');
        self::assertSame(1000, $regularPoints->amount());

        // Jacket with 2x multiplier: 100 PLN = 2000 points
        $jacketPoints = $practice->calculatePoints(Money::of(10000), 'JACKET-001');
        self::assertSame(2000, $jacketPoints->amount());
    }

    public function testRoundsDownByDefault(): void
    {
        $practice = AccountingPractice::forMarket(
            MarketId::fromString('PL'),
            'Poland',
            10,
            14,
            365,
            true // round down
        );

        // 5555 cents = 55.55 PLN = 555.5 points → 555 (rounded down)
        $points = $practice->calculatePoints(Money::of(5555));
        self::assertSame(555, $points->amount());
    }

    public function testCanRoundToNearest(): void
    {
        $practice = AccountingPractice::forMarket(
            MarketId::fromString('PL'),
            'Poland',
            10,
            14,
            365,
            false // round to nearest
        );

        // 5555 cents = 55.55 PLN = 555.5 points → 556 (rounded to nearest)
        $points = $practice->calculatePoints(Money::of(5555));
        self::assertSame(556, $points->amount());
    }

    public function testReturnsZeroForVerySmallAmounts(): void
    {
        $practice = AccountingPractice::forMarket(
            MarketId::fromString('PL'),
            'Poland',
            10,
            14
        );

        // 5 cents = 0.05 PLN = 0.5 points → 0
        $points = $practice->calculatePoints(Money::of(5));
        self::assertTrue($points->isZero());
    }

    public function testChecksForPromotionalMultiplier(): void
    {
        $practice = AccountingPractice::forMarket(
            MarketId::fromString('PL'),
            'Poland',
            10,
            14,
            365,
            true,
            ['JACKET-001' => 2.0]
        );

        self::assertTrue($practice->hasPromotionalMultiplier('JACKET-001'));
        self::assertFalse($practice->hasPromotionalMultiplier('SHIRT-001'));

        self::assertSame(2.0, $practice->promotionalMultiplier('JACKET-001'));
        self::assertSame(1.0, $practice->promotionalMultiplier('SHIRT-001')); // Default
    }

    public function testCannotCreateWithNegativePointsPerUnit(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Points per currency unit must be positive');

        AccountingPractice::forMarket(
            MarketId::fromString('PL'),
            'Poland',
            -10, // Negative!
            14
        );
    }

    public function testCannotCreateWithNegativeMaturationPeriod(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Maturation period cannot be negative');

        AccountingPractice::forMarket(
            MarketId::fromString('PL'),
            'Poland',
            10,
            -5 // Negative!
        );
    }

    public function testCannotCreateWithNegativeExpirationPeriod(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expiration period cannot be negative');

        AccountingPractice::forMarket(
            MarketId::fromString('PL'),
            'Poland',
            10,
            14,
            -365 // Negative!
        );
    }
}
