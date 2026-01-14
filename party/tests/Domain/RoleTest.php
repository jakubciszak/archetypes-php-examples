<?php

declare(strict_types=1);

namespace SoftwareArchetypes\Party\Tests\Domain;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use SoftwareArchetypes\Party\Role;

final class RoleTest extends TestCase
{
    public function testCanBeCreatedWithValidName(): void
    {
        $role = Role::of('Customer');

        self::assertEquals('Customer', $role->asString());
        self::assertEquals('Customer', $role->name());
    }

    public function testThrowsExceptionWhenNameIsNull(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Role name cannot be blank');

        new Role(null);
    }

    public function testThrowsExceptionWhenNameIsEmpty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Role name cannot be blank');

        new Role('');
    }

    public function testThrowsExceptionWhenNameIsOnlyWhitespace(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Role name cannot be blank');

        new Role('   ');
    }

    public function testTwoRolesWithSameNameAreEqual(): void
    {
        $role1 = Role::of('Admin');
        $role2 = Role::of('Admin');

        self::assertEquals($role1, $role2);
    }

    public function testTwoRolesWithDifferentNamesAreNotEqual(): void
    {
        $role1 = Role::of('Admin');
        $role2 = Role::of('User');

        self::assertNotEquals($role1, $role2);
    }
}
