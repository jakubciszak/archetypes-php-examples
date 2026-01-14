<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Pricing\Tests\Domain;

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use SoftwareArchetypes\Pricing\CalculatorId;

final class CalculatorIdTest extends TestCase
{
    public function testCanGenerateNewId(): void
    {
        $id = CalculatorId::generate();

        $this->assertInstanceOf(CalculatorId::class, $id);
    }

    public function testGeneratesUniqueIds(): void
    {
        $id1 = CalculatorId::generate();
        $id2 = CalculatorId::generate();

        $this->assertNotEquals($id1->toString(), $id2->toString());
    }

    public function testCanCreateFromUuid(): void
    {
        $uuid = Uuid::uuid4();
        $id = new CalculatorId($uuid);

        $this->assertInstanceOf(CalculatorId::class, $id);
        $this->assertEquals($uuid->toString(), $id->toString());
    }

    public function testToStringReturnsUuidString(): void
    {
        $uuid = Uuid::uuid4();
        $id = new CalculatorId($uuid);

        $this->assertEquals($uuid->toString(), $id->toString());
    }

    public function testTwoIdsWithSameUuidAreEqual(): void
    {
        $uuid = Uuid::uuid4();
        $id1 = new CalculatorId($uuid);
        $id2 = new CalculatorId($uuid);

        $this->assertEquals($id1, $id2);
    }
}
