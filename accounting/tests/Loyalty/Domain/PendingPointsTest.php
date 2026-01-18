<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Tests\Loyalty\Domain;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Accounting\Loyalty\Domain\PendingPoints;
use SoftwareArchetypes\Accounting\Loyalty\Domain\Points;
use SoftwareArchetypes\Accounting\Loyalty\Domain\PurchaseId;

final class PendingPointsTest extends TestCase
{
    public function testCreatesPendingPoints(): void
    {
        $purchaseId = PurchaseId::generate();
        $points = Points::of(100);
        $purchaseDate = new DateTimeImmutable('2024-01-01');
        $activationDate = new DateTimeImmutable('2024-01-15');

        $pending = PendingPoints::forPurchase(
            $purchaseId,
            $points,
            $purchaseDate,
            $activationDate,
            'Purchase in Poland',
        );

        self::assertTrue($pending->purchaseId()->equals($purchaseId));
        self::assertTrue($pending->points()->equals($points));
        self::assertSame('2024-01-01', $pending->purchaseDate()->format('Y-m-d'));
        self::assertSame('2024-01-15', $pending->activationDate()->format('Y-m-d'));
        self::assertSame('Purchase in Poland', $pending->reason());
        self::assertFalse($pending->isActivated());
        self::assertFalse($pending->isReversed());
    }

    public function testCannotCreateWithActivationBeforePurchase(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Activation date cannot be before purchase date');

        PendingPoints::forPurchase(
            PurchaseId::generate(),
            Points::of(100),
            new DateTimeImmutable('2024-01-15'),
            new DateTimeImmutable('2024-01-01'), // Before purchase
        );
    }

    public function testCanActivateAfterActivationDate(): void
    {
        $pending = PendingPoints::forPurchase(
            PurchaseId::generate(),
            Points::of(100),
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-01-15'),
        );

        $currentDate = new DateTimeImmutable('2024-01-20');

        self::assertTrue($pending->canActivate($currentDate));
    }

    public function testCannotActivateBeforeActivationDate(): void
    {
        $pending = PendingPoints::forPurchase(
            PurchaseId::generate(),
            Points::of(100),
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-01-15'),
        );

        $currentDate = new DateTimeImmutable('2024-01-10');

        self::assertFalse($pending->canActivate($currentDate));
    }

    public function testActivatesPoints(): void
    {
        $pending = PendingPoints::forPurchase(
            PurchaseId::generate(),
            Points::of(100),
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-01-15'),
        );

        $pending->activate();

        self::assertTrue($pending->isActivated());
    }

    public function testCannotActivateTwice(): void
    {
        $pending = PendingPoints::forPurchase(
            PurchaseId::generate(),
            Points::of(100),
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-01-15'),
        );

        $pending->activate();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Points already activated');

        $pending->activate();
    }

    public function testReversesPoints(): void
    {
        $pending = PendingPoints::forPurchase(
            PurchaseId::generate(),
            Points::of(100),
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-01-15'),
        );

        $pending->reverse();

        self::assertTrue($pending->isReversed());
    }

    public function testCannotReverseTwice(): void
    {
        $pending = PendingPoints::forPurchase(
            PurchaseId::generate(),
            Points::of(100),
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-01-15'),
        );

        $pending->reverse();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Points already reversed');

        $pending->reverse();
    }

    public function testCannotActivateReversedPoints(): void
    {
        $pending = PendingPoints::forPurchase(
            PurchaseId::generate(),
            Points::of(100),
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-01-15'),
        );

        $pending->reverse();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot activate reversed points');

        $pending->activate();
    }

    public function testCannotReverseActivatedPoints(): void
    {
        $pending = PendingPoints::forPurchase(
            PurchaseId::generate(),
            Points::of(100),
            new DateTimeImmutable('2024-01-01'),
            new DateTimeImmutable('2024-01-15'),
        );

        $pending->activate();

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Cannot reverse activated points');

        $pending->reverse();
    }
}
