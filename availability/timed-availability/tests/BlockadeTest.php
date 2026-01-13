<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Availability\TimedAvailability\Tests;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Availability\TimedAvailability\Blockade;
use SoftwareArchetypes\Availability\TimedAvailability\Owner;

final class BlockadeTest extends TestCase
{
    public function testCanCreateNoneBlockade(): void
    {
        $blockade = Blockade::none();

        self::assertTrue($blockade->takenBy()->byNone());
        self::assertFalse($blockade->disabled());
    }

    public function testCanCreateOwnedBlockade(): void
    {
        $owner = Owner::newOne();
        $blockade = Blockade::ownedBy($owner);

        self::assertTrue($blockade->takenBy()->equals($owner));
        self::assertFalse($blockade->disabled());
    }

    public function testCanCreateDisabledBlockade(): void
    {
        $owner = Owner::newOne();
        $blockade = Blockade::disabledBy($owner);

        self::assertTrue($blockade->takenBy()->equals($owner));
        self::assertTrue($blockade->disabled());
    }

    public function testNoneBlockadeCanBeTakenByAnyone(): void
    {
        $blockade = Blockade::none();
        $requester = Owner::newOne();

        self::assertTrue($blockade->canBeTakenBy($requester));
    }

    public function testOwnedBlockadeCanBeTakenByOwner(): void
    {
        $owner = Owner::newOne();
        $blockade = Blockade::ownedBy($owner);

        self::assertTrue($blockade->canBeTakenBy($owner));
    }

    public function testOwnedBlockadeCannotBeTakenByDifferentOwner(): void
    {
        $owner = Owner::newOne();
        $otherOwner = Owner::newOne();
        $blockade = Blockade::ownedBy($owner);

        self::assertFalse($blockade->canBeTakenBy($otherOwner));
    }

    public function testDisabledBlockadeCanBeTakenByOwner(): void
    {
        $owner = Owner::newOne();
        $blockade = Blockade::disabledBy($owner);

        self::assertTrue($blockade->canBeTakenBy($owner));
    }

    public function testDisabledBlockadeCannotBeTakenByDifferentOwner(): void
    {
        $owner = Owner::newOne();
        $otherOwner = Owner::newOne();
        $blockade = Blockade::disabledBy($owner);

        self::assertFalse($blockade->canBeTakenBy($otherOwner));
    }

    public function testIsDisabledByOwner(): void
    {
        $owner = Owner::newOne();
        $blockade = Blockade::disabledBy($owner);

        self::assertTrue($blockade->isDisabledBy($owner));
    }

    public function testIsNotDisabledByDifferentOwner(): void
    {
        $owner = Owner::newOne();
        $otherOwner = Owner::newOne();
        $blockade = Blockade::disabledBy($owner);

        self::assertFalse($blockade->isDisabledBy($otherOwner));
    }

    public function testIsNotDisabledWhenOnlyOwned(): void
    {
        $owner = Owner::newOne();
        $blockade = Blockade::ownedBy($owner);

        self::assertFalse($blockade->isDisabledBy($owner));
    }
}
