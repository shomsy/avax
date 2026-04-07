<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\Access;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Capability\Access\Access;
use Avax\Auth\System\Capability\Access\RequireAuthentication\RequireAuthentication;
use Avax\Auth\System\Capability\Access\RequirePermission\RequirePermission;
use Avax\Auth\System\Capability\Access\RequireRole\RequireRole;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserEmail;
use Avax\Auth\System\Capability\User\UserPermission;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\User\UserRole;
use Avax\Auth\System\Flow\CheckAuthentication\CheckAuthentication;
use Avax\Auth\System\Flow\ReadCurrentUser\ReadCurrentUser;
use Mockery;

/**
 * Unit test for the Access façade.
 */
class AccessTest extends TestCase
{
    protected function tearDown() : void
    {
        Mockery::close();
    }

    public function testAccessFacadeDelegatesToBoundaries() : void
    {
        $user = new User(
            id: new UserId(value: 42),
            email: new UserEmail(value: 'access@example.com'),
            username: 'access',
            passwordHash: 'hash',
            roles: [UserRole::ADMIN],
            permissions: [new UserPermission(value: 'write')]
        );

        $checkAuthentication = Mockery::mock(CheckAuthentication::class);
        $checkAuthentication->shouldReceive('execute')->once()->andReturn(true);

        $readCurrentUser = Mockery::mock(ReadCurrentUser::class);
        $readCurrentUser->shouldReceive('execute')->twice()->andReturn($user);

        $access = new Access(
            requireAuthentication: new RequireAuthentication(checkAuthentication: $checkAuthentication),
            requireRole: new RequireRole(readCurrentUser: $readCurrentUser),
            requirePermission: new RequirePermission(readCurrentUser: $readCurrentUser)
        );

        $access->requireAuthentication();
        $access->requireRole(requiredRole: UserRole::USER);
        $access->requirePermission(permission: new UserPermission(value: 'write'));

        $this->assertTrue(condition: true);
    }
}
