<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Identity\Auth\Capabilities\Identity\User;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserPermission;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserRole;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

final class UserTest extends TestCase
{
    #[Test]
    public function test_user_can_be_created(): void
    {
        $user = new User(
            id: new UserId(1),
            email: new UserEmail('milos@example.com'),
            username: 'milos',
            passwordHash: 'hashed',
        );

        self::assertSame(1, $user->id->value);
        self::assertSame('milos@example.com', $user->email->value);
        self::assertSame('milos', $user->username);
        self::assertTrue($user->isActive());
    }

    #[Test]
    public function test_user_can_be_created_inactive(): void
    {
        $user = new User(
            id: new UserId(1),
            email: new UserEmail('locked@example.com'),
            username: 'locked',
            passwordHash: 'hashed',
            isActive: false,
        );

        self::assertFalse($user->isActive());
    }

    #[Test]
    public function test_user_has_roles(): void
    {
        $user = new User(
            id: new UserId(1),
            email: new UserEmail('milos@example.com'),
            username: 'milos',
            passwordHash: 'hashed',
            roles: [UserRole::ADMIN],
        );

        self::assertTrue($user->hasRole(UserRole::ADMIN));
        self::assertCount(1, $user->getRoles());
    }

    #[Test]
    public function test_user_has_permissions(): void
    {
        $permission = new UserPermission('read-users');
        $user = new User(
            id: new UserId(1),
            email: new UserEmail('milos@example.com'),
            username: 'milos',
            passwordHash: 'hashed',
            permissions: [$permission],
        );

        self::assertTrue($user->hasPermission($permission));
        self::assertCount(1, $user->getPermissions());
    }
}
