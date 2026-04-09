<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\Access\RequireRole;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Capability\Access\RequireRole\RequireRole;
use Avax\Auth\System\Capability\Access\RequireRole\RoleDenied;
use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Flow\ReadCurrentUser\ReadCurrentUser;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserRole;
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
     * @throws \Avax\Auth\System\Capability\Access\RequireRole\RoleDenied
     * @throws Unauthenticated
     */
    public function testRequireRoleSuccess() : void
    {
        $role = UserRole::ADMIN;
        $user = Mockery::mock(User::class);
        $user->shouldReceive('canAccessRole')
            ->with($role)
            ->andReturn(true);

        $readCurrentUser = Mockery::mock(ReadCurrentUser::class);
        $readCurrentUser->shouldReceive('execute')->andReturn($user);

        $requirement = new RequireRole(readCurrentUser: $readCurrentUser);
        $requirement->execute(requiredRole: $role);

        $this->assertTrue(condition: true); // No exception thrown
    }

    /**
     * @throws Unauthenticated
     */
    public function testRequireRoleFailure() : void
    {
        $role = UserRole::ADMIN;
        $user = Mockery::mock(User::class);
        $user->shouldReceive('canAccessRole')
            ->with($role)
            ->andReturn(false);

        $readCurrentUser = Mockery::mock(ReadCurrentUser::class);
        $readCurrentUser->shouldReceive('execute')->andReturn($user);

        $requirement = new RequireRole(readCurrentUser: $readCurrentUser);

        $this->expectException(exception: RoleDenied::class);

        $requirement->execute(requiredRole: $role);
    }

    /**
     * @throws RoleDenied
     */
    public function testRequireRoleFailureUserNotLoggedIn() : void
    {
        $role = UserRole::ADMIN;

        $readCurrentUser = Mockery::mock(ReadCurrentUser::class);
        $readCurrentUser->shouldReceive('execute')->andReturn(null);

        $requirement = new RequireRole(readCurrentUser: $readCurrentUser);

        $this->expectException(exception: Unauthenticated::class);

        $requirement->execute(requiredRole: $role);
    }
}
