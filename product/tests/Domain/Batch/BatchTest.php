<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Domain\Batch;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Product\Batch\Batch;
use SoftwareArchetypes\Product\Batch\BatchId;
use SoftwareArchetypes\Product\Batch\BatchName;

final class BatchTest extends TestCase
{
    public function testCanBeCreatedWithIdAndName(): void
    {
        $id = BatchId::random();
        $name = BatchName::of('BATCH-001');
        $batch = new Batch($id, $name);

        self::assertSame($id, $batch->id());
        self::assertSame($name, $batch->name());
    }

    public function testCanBeCreatedWithIdNameAndProductionDate(): void
    {
        $id = BatchId::random();
        $name = BatchName::of('BATCH-001');
        $productionDate = new \DateTimeImmutable('2025-01-15');
        $batch = new Batch($id, $name, $productionDate);

        self::assertSame($id, $batch->id());
        self::assertSame($name, $batch->name());
        self::assertEquals($productionDate, $batch->productionDate());
    }

    public function testCanBeCreatedWithIdNameProductionDateAndExpiryDate(): void
    {
        $id = BatchId::random();
        $name = BatchName::of('BATCH-001');
        $productionDate = new \DateTimeImmutable('2025-01-15');
        $expiryDate = new \DateTimeImmutable('2026-01-15');
        $batch = new Batch($id, $name, $productionDate, $expiryDate);

        self::assertSame($id, $batch->id());
        self::assertSame($name, $batch->name());
        self::assertEquals($productionDate, $batch->productionDate());
        self::assertEquals($expiryDate, $batch->expiryDate());
    }

    public function testProductionDateIsNullByDefault(): void
    {
        $id = BatchId::random();
        $name = BatchName::of('BATCH-001');
        $batch = new Batch($id, $name);

        self::assertNull($batch->productionDate());
    }

    public function testExpiryDateIsNullByDefault(): void
    {
        $id = BatchId::random();
        $name = BatchName::of('BATCH-001');
        $batch = new Batch($id, $name);

        self::assertNull($batch->expiryDate());
    }

    public function testRejectsExpiryDateBeforeProductionDate(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Expiry date cannot be before production date');

        $id = BatchId::random();
        $name = BatchName::of('BATCH-001');
        $productionDate = new \DateTimeImmutable('2025-01-15');
        $expiryDate = new \DateTimeImmutable('2024-12-31');

        new Batch($id, $name, $productionDate, $expiryDate);
    }

    public function testAcceptsSameProductionAndExpiryDate(): void
    {
        $id = BatchId::random();
        $name = BatchName::of('BATCH-001');
        $date = new \DateTimeImmutable('2025-01-15');
        $batch = new Batch($id, $name, $date, $date);

        self::assertEquals($date, $batch->productionDate());
        self::assertEquals($date, $batch->expiryDate());
    }

    public function testTwoBatchesWithSameIdAndNameAreEqual(): void
    {
        $id = BatchId::random();
        $name = BatchName::of('BATCH-001');
        $batch1 = new Batch($id, $name);
        $batch2 = new Batch($id, $name);

        self::assertEquals($batch1, $batch2);
    }
}
