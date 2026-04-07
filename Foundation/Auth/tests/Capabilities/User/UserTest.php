<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capabilities\User;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Capabilities\User\UserEmail;
use Avax\Auth\System\Capabilities\User\UserId;
use Avax\Auth\System\Capabilities\User\UserRole;
use Avax\Auth\System\Capabilities\User\UserPermission;
use Avax\Auth\System\Capabilities\User\User;
use InvalidArgumentException;

/**
 * Unit test for User capability units (Value Objects and User entity).
 */
class UserTest extends TestCase
{
    public function testUserEmailValidation() : void
    {
        $email = new UserEmail('test@example.com');
        $this->assertEquals('test@example.com', (string) $email);

        $this->expectException(InvalidArgumentException::class);
        new UserEmail('invalid-email');
    }

    public function testUserEmailEquality() : void
    {
        $email1 = new UserEmail('TEST@example.com');
        $email2 = new UserEmail('test@example.com');
        $this->assertTrue($email1->equals($email2));
    }

    public function testUserId() : void
    {
        $id = new UserId(123);
        $this->assertEquals('123', (string) $id);
        $this->assertTrue($id->equals(new UserId(123)));
        $this->assertFalse($id->equals(new UserId(456)));
    }

    public function testUserRoleHandling() : void
    {
        $role = UserRole::ADMIN;
        $this->assertEquals('admin', $role->value);
        $this->assertTrue($role->canAccess(UserRole::USER));
        $this->assertFalse(UserRole::USER->canAccess(UserRole::ADMIN));
    }

    public function testUserCreationAndState() : void
    {
        $user = User::create(
            id: new UserId(1),
            email: new UserEmail('user@test.com'),
            username: 'tester',
            passwordHash: 'hash',
            isActive: true
        );

        $this->assertTrue($user->isActive());
        
        $inactiveUser = User::create(
            id: $user->id,
            email: $user->email,
            username: $user->username,
            passwordHash: $user->passwordHash,
            isActive: false
        );
        $this->assertFalse($inactiveUser->isActive());

        $adminUser = User::create(
            id: $user->id,
            email: $user->email,
            username: $user->username,
            passwordHash: $user->passwordHash,
            roles: [UserRole::ADMIN]
        );
        $this->assertTrue($adminUser->hasRole(UserRole::ADMIN));
        $this->assertTrue($adminUser->canAccessRole(UserRole::USER));
    }

    public function testUserPermissions() : void
    {
        $permission = new UserPermission('write');
        $user = User::create(
            id: new UserId(1),
            email: new UserEmail('user@test.com'),
            username: 'tester',
            passwordHash: 'hash',
            permissions: [$permission]
        );

        $this->assertTrue($user->hasPermission($permission));
    }
}
