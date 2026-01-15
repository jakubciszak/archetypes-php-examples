<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Domain\Batch;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Product\Batch\BatchName;

final class BatchNameTest extends TestCase
{
    public function testCanBeCreatedWithValidValue(): void
    {
        $batchName = BatchName::of('BATCH-2025-001');

        self::assertEquals('BATCH-2025-001', $batchName->value());
        self::assertEquals('BATCH-2025-001', $batchName->asString());
    }

    public function testRejectsEmptyValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Batch name cannot be empty');

        BatchName::of('');
    }

    public function testRejectsWhitespaceOnlyValue(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Batch name cannot be empty');

        BatchName::of('   ');
    }

    public function testTrimsWhitespace(): void
    {
        $batchName = BatchName::of('  LOT-123  ');

        self::assertEquals('LOT-123', $batchName->value());
    }

    public function testTwoBatchNamesWithSameValueAreEqual(): void
    {
        $name1 = BatchName::of('BATCH-001');
        $name2 = BatchName::of('BATCH-001');

        self::assertEquals($name1, $name2);
    }
}
