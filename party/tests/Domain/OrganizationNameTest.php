<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party\Tests\Domain;

use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Party\OrganizationName;

final class OrganizationNameTest extends TestCase
{
    public function testCanBeCreatedWithValidValue(): void
    {
        $organizationName = OrganizationName::of('Acme Corporation');

        self::assertEquals('Acme Corporation', $organizationName->value());
        self::assertEquals('Acme Corporation', $organizationName->asString());
    }

    public function testTwoOrganizationNamesWithSameValueAreEqual(): void
    {
        $name1 = OrganizationName::of('Tech Solutions Ltd');
        $name2 = OrganizationName::of('Tech Solutions Ltd');

        self::assertEquals($name1, $name2);
    }

    public function testTwoOrganizationNamesWithDifferentValuesAreNotEqual(): void
    {
        $name1 = OrganizationName::of('Company A');
        $name2 = OrganizationName::of('Company B');

        self::assertNotEquals($name1, $name2);
    }
}
