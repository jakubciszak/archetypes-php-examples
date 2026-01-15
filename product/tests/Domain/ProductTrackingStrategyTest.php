<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Domain;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Product\ProductTrackingStrategy;

final class ProductTrackingStrategyTest extends TestCase
{
    public function testUniqueStrategy(): void
    {
        $strategy = ProductTrackingStrategy::UNIQUE;

        self::assertTrue($strategy->isTrackedIndividually());
        self::assertFalse($strategy->isTrackedByBatch());
        self::assertFalse($strategy->requiresBothTrackingMethods());
        self::assertFalse($strategy->isInterchangeable());
    }

    public function testIndividuallyTrackedStrategy(): void
    {
        $strategy = ProductTrackingStrategy::INDIVIDUALLY_TRACKED;

        self::assertTrue($strategy->isTrackedIndividually());
        self::assertFalse($strategy->isTrackedByBatch());
        self::assertFalse($strategy->requiresBothTrackingMethods());
        self::assertFalse($strategy->isInterchangeable());
    }

    public function testBatchTrackedStrategy(): void
    {
        $strategy = ProductTrackingStrategy::BATCH_TRACKED;

        self::assertFalse($strategy->isTrackedIndividually());
        self::assertTrue($strategy->isTrackedByBatch());
        self::assertFalse($strategy->requiresBothTrackingMethods());
        self::assertFalse($strategy->isInterchangeable());
    }

    public function testIndividuallyAndBatchTrackedStrategy(): void
    {
        $strategy = ProductTrackingStrategy::INDIVIDUALLY_AND_BATCH_TRACKED;

        self::assertTrue($strategy->isTrackedIndividually());
        self::assertTrue($strategy->isTrackedByBatch());
        self::assertTrue($strategy->requiresBothTrackingMethods());
        self::assertFalse($strategy->isInterchangeable());
    }

    public function testIdenticalStrategy(): void
    {
        $strategy = ProductTrackingStrategy::IDENTICAL;

        self::assertFalse($strategy->isTrackedIndividually());
        self::assertFalse($strategy->isTrackedByBatch());
        self::assertFalse($strategy->requiresBothTrackingMethods());
        self::assertTrue($strategy->isInterchangeable());
    }
}
