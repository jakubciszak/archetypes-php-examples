<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Product\Tests\Domain\Identifier;

use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use SoftwareArchetypes\Product\Identifier\UuidProductIdentifier;

final class UuidProductIdentifierTest extends TestCase
{
    public function testCanBeCreatedWithUuid(): void
    {
        $uuid = Uuid::uuid4();
        $identifier = UuidProductIdentifier::of($uuid);

        self::assertEquals($uuid->toString(), $identifier->asString());
        self::assertSame($uuid, $identifier->value());
    }

    public function testCanGenerateRandomIdentifier(): void
    {
        $id1 = UuidProductIdentifier::random();
        $id2 = UuidProductIdentifier::random();

        self::assertNotEquals($id1->asString(), $id2->asString());
    }

    public function testTwoIdentifiersWithSameUuidAreEqual(): void
    {
        $uuid = Uuid::uuid4();
        $id1 = UuidProductIdentifier::of($uuid);
        $id2 = UuidProductIdentifier::of($uuid);

        self::assertEquals($id1, $id2);
    }

    public function testProvidesIdentifierType(): void
    {
        $identifier = UuidProductIdentifier::random();

        self::assertEquals('UUID', $identifier->type());
    }
}
