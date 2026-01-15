<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Domain\Batch;

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use SoftwareArchetypes\Product\Batch\BatchId;

final class BatchIdTest extends TestCase
{
    public function testCanBeCreatedWithUuid(): void
    {
        $uuid = Uuid::uuid4();
        $batchId = BatchId::of($uuid);

        self::assertEquals($uuid->toString(), $batchId->asString());
        self::assertSame($uuid, $batchId->value());
    }

    public function testCanGenerateRandomId(): void
    {
        $id1 = BatchId::random();
        $id2 = BatchId::random();

        self::assertNotEquals($id1->asString(), $id2->asString());
    }

    public function testTwoIdsWithSameUuidAreEqual(): void
    {
        $uuid = Uuid::uuid4();
        $id1 = BatchId::of($uuid);
        $id2 = BatchId::of($uuid);

        self::assertEquals($id1, $id2);
    }
}
