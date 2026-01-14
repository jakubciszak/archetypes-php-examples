<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party\Tests\Domain;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Party\PersonalData;

final class PersonalDataTest extends TestCase
{
    public function testCanBeCreatedWithValidNames(): void
    {
        $personalData = PersonalData::from('John', 'Doe');

        self::assertEquals('John', $personalData->firstName());
        self::assertEquals('Doe', $personalData->lastName());
    }

    public function testCanBeCreatedWithEmptyMethod(): void
    {
        $personalData = PersonalData::empty();

        self::assertEquals('', $personalData->firstName());
        self::assertEquals('', $personalData->lastName());
    }

    public function testNullFirstNameIsReplacedWithEmptyString(): void
    {
        $personalData = new PersonalData(null, 'Doe');

        self::assertEquals('', $personalData->firstName());
        self::assertEquals('Doe', $personalData->lastName());
    }

    public function testNullLastNameIsReplacedWithEmptyString(): void
    {
        $personalData = new PersonalData('John', null);

        self::assertEquals('John', $personalData->firstName());
        self::assertEquals('', $personalData->lastName());
    }

    public function testBothNullNamesAreReplacedWithEmptyStrings(): void
    {
        $personalData = new PersonalData(null, null);

        self::assertEquals('', $personalData->firstName());
        self::assertEquals('', $personalData->lastName());
    }

    public function testTwoPersonalDataWithSameValuesAreEqual(): void
    {
        $data1 = PersonalData::from('Jane', 'Smith');
        $data2 = PersonalData::from('Jane', 'Smith');

        self::assertEquals($data1, $data2);
    }

    public function testTwoPersonalDataWithDifferentValuesAreNotEqual(): void
    {
        $data1 = PersonalData::from('Jane', 'Smith');
        $data2 = PersonalData::from('John', 'Doe');

        self::assertNotEquals($data1, $data2);
    }
}
