<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Tests\Loyalty\Domain;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Accounting\Loyalty\Domain\MarketId;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Points;
use SoftwareArchetypes\Accounting\Loyalty\Domain\PostingRule;
use SoftwareArchetypes\Accounting\Money;

final class PostingRuleTest extends TestCase
{
    public function testCreatesPostingRule(): void
    {
        $marketId = MarketId::fromString('PL');
        $rule = PostingRule::create(
            $marketId,
            'Poland',
            10, // 10 points per PLN
            14, // 14 days return period
        );

        self::assertTrue($rule->marketId()->equals($marketId));
        self::assertSame('Poland', $rule->marketName());
        self::assertSame(10, $rule->pointsPerCurrencyUnit());
        self::assertSame(14, $rule->returnPeriodDays());
    }

    public function testCalculatesPointsForPurchase(): void
    {
        $rule = PostingRule::create(
            MarketId::fromString('PL'),
            'Poland',
            10, // 10 points per PLN
            14,
        );

        // 5000 cents = 50 PLN = 500 points
        $points = $rule->calculatePoints(Money::of(5000));

        self::assertSame(500, $points->amount());
    }

    public function testCalculatesPointsForDifferentMarkets(): void
    {
        $rulePL = PostingRule::create(
            MarketId::fromString('PL'),
            'Poland',
            10,
            14,
        );

        $ruleDE = PostingRule::create(
            MarketId::fromString('DE'),
            'Germany',
            15, // 15 points per EUR
            14,
        );

        // Same money amount, different points
        $amount = Money::of(10000); // 100 currency units

        self::assertSame(1000, $rulePL->calculatePoints($amount)->amount());
        self::assertSame(1500, $ruleDE->calculatePoints($amount)->amount());
    }

    public function testCannotCreateRuleWithNegativePointsPerUnit(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Points per currency unit must be positive');

        PostingRule::create(
            MarketId::fromString('PL'),
            'Poland',
            -10,
            14,
        );
    }

    public function testCannotCreateRuleWithNegativeReturnPeriod(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Return period cannot be negative');

        PostingRule::create(
            MarketId::fromString('PL'),
            'Poland',
            10,
            -5,
        );
    }
}
