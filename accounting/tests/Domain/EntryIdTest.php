<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Accounting\Tests\Domain;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Accounting\Domain\EntryId;

class EntryIdTest extends TestCase
{
    public function testCanCreateEntryIdFromString(): void
    {
        $id = EntryId::fromString('entry-123');

        $this->assertEquals('entry-123', $id->toString());
    }

    public function testCanGenerateRandomEntryId(): void
    {
        $id1 = EntryId::generate();
        $id2 = EntryId::generate();

        $this->assertNotEquals($id1->toString(), $id2->toString());
        $this->assertNotEmpty($id1->toString());
    }

    public function testTwoEntryIdsWithSameValueAreEqual(): void
    {
        $id1 = EntryId::fromString('entry-123');
        $id2 = EntryId::fromString('entry-123');

        $this->assertTrue($id1->equals($id2));
    }

    public function testCannotCreateEntryIdWithEmptyString(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Entry ID cannot be empty');

        EntryId::fromString('');
    }
}
