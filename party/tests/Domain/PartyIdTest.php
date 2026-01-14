<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party\Tests\Domain;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Ramsey\Uuid\Uuid;
use SoftwareArchetypes\Party\PartyId;

final class PartyIdTest extends TestCase
{
    public function testCanBeCreatedFromValidUuid(): void
    {
        $uuid = Uuid::uuid4();
        $partyId = PartyId::of($uuid);

        self::assertEquals($uuid->toString(), $partyId->asString());
        self::assertEquals($uuid, $partyId->value());
    }

    public function testCanBeCreatedAsRandom(): void
    {
        $partyId = PartyId::random();

        self::assertInstanceOf(PartyId::class, $partyId);
        self::assertNotEmpty($partyId->asString());
    }

    public function testThrowsExceptionWhenUuidIsNull(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Party Id value cannot be null');

        new PartyId(null);
    }

    public function testTwoPartyIdsWithSameUuidAreEqual(): void
    {
        $uuid = Uuid::uuid4();
        $partyId1 = PartyId::of($uuid);
        $partyId2 = PartyId::of($uuid);

        self::assertEquals($partyId1, $partyId2);
    }

    public function testTwoPartyIdsWithDifferentUuidsAreNotEqual(): void
    {
        $partyId1 = PartyId::random();
        $partyId2 = PartyId::random();

        self::assertNotEquals($partyId1, $partyId2);
    }
}
