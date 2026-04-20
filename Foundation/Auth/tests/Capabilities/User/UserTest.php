<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capabilities\User;

use Avax\Auth\System\Capabilities\Identity\User\User;
use Avax\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Auth\System\Capabilities\Identity\User\UserPermission;
use Avax\Auth\System\Capabilities\Identity\User\UserRole;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for User capability units (Value Objects and User entity).
 */
class UserTest extends TestCase
{
    public function testUserEmailValidation() : void
    {
        $email = new UserEmail(value: 'test@example.com');
        $this->assertEquals(expected: 'test@example.com', actual: (string) $email);

        $this->expectException(exception: InvalidArgumentException::class);
        new UserEmail(value: 'invalid-email');
    }

    public function testUserEmailEquality() : void
    {
        $email1 = new UserEmail(value: 'TEST@example.com');
        $email2 = new UserEmail(value: 'test@example.com');
        $this->assertTrue(condition: $email1->equals(other: $email2));
    }

    public function testUserId() : void
    {
        $id = new UserId(value: 123);
        $this->assertEquals(expected: '123', actual: (string) $id);
        $this->assertTrue(condition: $id->equals(other: new UserId(value: 123)));
        $this->assertFalse(condition: $id->equals(other: new UserId(value: 456)));
    }

    public function testUserRoleHandling() : void
    {
        $role = UserRole::ADMIN;
        $this->assertEquals(expected: 'admin', actual: $role->value);
        $this->assertTrue(condition: $role->canAccess(required: UserRole::USER));
        $this->assertFalse(condition: UserRole::USER->canAccess(required: UserRole::ADMIN));
    }

    public function testUserCreationAndState() : void
    {
        $user = User::create(
            id          : new UserId(value: 1),
            email       : new UserEmail(value: 'user@test.com'),
            username    : 'tester',
            passwordHash: 'hash',
            isActive    : true
        );

        $this->assertTrue(condition: $user->isActive());

        $inactiveUser = User::create(
            id          : $user->id,
            email       : $user->email,
            username    : $user->username,
            passwordHash: $user->passwordHash,
            isActive    : false
        );
        $this->assertFalse(condition: $inactiveUser->isActive());

        $adminUser = User::create(
            id          : $user->id,
            email       : $user->email,
            username    : $user->username,
            passwordHash: $user->passwordHash,
            roles       : [UserRole::ADMIN]
        );
        $this->assertTrue(condition: $adminUser->hasRole(role: UserRole::ADMIN));
        $this->assertTrue(condition: $adminUser->canAccessRole(requiredRole: UserRole::USER));
    }

    public function testUserPermissions() : void
    {
        $permission = new UserPermission(value: 'write');
        $user       = User::create(
            id          : new UserId(value: 1),
            email       : new UserEmail(value: 'user@test.com'),
            username    : 'tester',
            passwordHash: 'hash',
            permissions : [$permission]
        );

        $this->assertTrue(condition: $user->hasPermission(permission: $permission));
    }
}
