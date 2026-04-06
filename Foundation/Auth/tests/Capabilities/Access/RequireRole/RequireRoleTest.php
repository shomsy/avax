<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capabilities\Access\RequireRole;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Capabilities\Access\RequireRole\RequireRole;
use Avax\Auth\System\Capabilities\Access\RequireRole\RoleDenied;
use Avax\Auth\System\Flows\ReadCurrentUser\ReadCurrentUser;
use Avax\Auth\System\Capabilities\User\User;
use Avax\Auth\System\Capabilities\User\UserRole;
use Mockery;

/**
 * Unit test for RequireRole access boundary.
 */
class RequireRoleTest extends TestCase
{
    protected function tearDown() : void
    {
        Mockery::close();
    }

    /**
     * @throws \Avax\Auth\System\Capabilities\Access\RequireRole\RoleDenied
     */
    public function testRequireRoleSuccess() : void
    {
        $role = UserRole::ADMIN;
        $user = Mockery::mock(User::class);
        $user->shouldReceive('hasRole')
            ->with($role)
            ->andReturn(true);

        $readCurrentUser = Mockery::mock(ReadCurrentUser::class);
        $readCurrentUser->shouldReceive('execute')->andReturn($user);

        $requirement = new RequireRole(readCurrentUser: $readCurrentUser);
        $requirement->execute(requiredRole: $role);

        $this->assertTrue(true); // No exception thrown
    }

    public function testRequireRoleFailure() : void
    {
        $role = UserRole::ADMIN;
        $user = Mockery::mock(User::class);
        $user->shouldReceive('hasRole')
            ->with($role)
            ->andReturn(false);

        $readCurrentUser = Mockery::mock(ReadCurrentUser::class);
        $readCurrentUser->shouldReceive('execute')->andReturn($user);

        $requirement = new RequireRole(readCurrentUser: $readCurrentUser);

        $this->expectException(RoleDenied::class);

        $requirement->execute(requiredRole: $role);
    }
}
