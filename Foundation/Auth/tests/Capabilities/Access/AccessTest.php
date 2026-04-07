<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capabilities\Access;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Capabilities\Access\Access;
use Avax\Auth\System\Capabilities\Access\RequireAuthentication\RequireAuthentication;
use Avax\Auth\System\Capabilities\Access\RequirePermission\RequirePermission;
use Avax\Auth\System\Capabilities\Access\RequireRole\RequireRole;
use Avax\Auth\System\Capabilities\User\User;
use Avax\Auth\System\Capabilities\User\UserEmail;
use Avax\Auth\System\Capabilities\User\UserPermission;
use Avax\Auth\System\Capabilities\User\UserId;
use Avax\Auth\System\Capabilities\User\UserRole;
use Avax\Auth\System\Flows\CheckAuthentication\CheckAuthentication;
use Avax\Auth\System\Flows\ReadCurrentUser\ReadCurrentUser;
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
            id: new UserId(42),
            email: new UserEmail('access@example.com'),
            username: 'access',
            passwordHash: 'hash',
            roles: [UserRole::ADMIN],
            permissions: [new UserPermission('write')]
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
        $access->requireRole(UserRole::USER);
        $access->requirePermission(new UserPermission('write'));

        $this->assertTrue(true);
    }
}
