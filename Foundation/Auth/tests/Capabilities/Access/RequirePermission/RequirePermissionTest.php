<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\Access\RequirePermission;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\Access\RequirePermission\RequirePermission;
use Avax\Auth\System\Capability\Access\RequirePermission\PermissionDenied;
use Avax\Auth\System\Flow\ReadCurrentUser\ReadCurrentUser;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserPermission;
use Mockery;

/**
 * Unit test for RequirePermission access boundary.
 */
class RequirePermissionTest extends TestCase
{
    protected function tearDown() : void
    {
        Mockery::close();
    }

    public function testRequirePermissionSuccess() : void
    {
        $permission = new UserPermission(value: 'delete_user');
        $user = Mockery::mock(User::class);
        $user->shouldReceive('hasPermission')
            ->with($permission)
            ->andReturn(true);

        $readCurrentUser = Mockery::mock(ReadCurrentUser::class);
        $readCurrentUser->shouldReceive('execute')->andReturn($user);

        $requirement = new RequirePermission(readCurrentUser: $readCurrentUser);
        $requirement->execute(permission: $permission);

        $this->assertTrue(condition: true); // No exception thrown
    }

    public function testRequirePermissionFailure() : void
    {
        $permission = new UserPermission(value: 'delete_user');
        $user = Mockery::mock(User::class);
        $user->shouldReceive('hasPermission')
            ->with($permission)
            ->andReturn(false);

        $readCurrentUser = Mockery::mock(ReadCurrentUser::class);
        $readCurrentUser->shouldReceive('execute')->andReturn($user);

        $requirement = new RequirePermission(readCurrentUser: $readCurrentUser);

        $this->expectException(exception: PermissionDenied::class);

        $requirement->execute(permission: $permission);
    }

    public function testRequirePermissionFailureUserNotLoggedIn() : void
    {
        $permission = new UserPermission(value: 'delete_user');

        $readCurrentUser = Mockery::mock(ReadCurrentUser::class);
        $readCurrentUser->shouldReceive('execute')->andReturn(null);

        $requirement = new RequirePermission(readCurrentUser: $readCurrentUser);

        $this->expectException(exception: Unauthenticated::class);

        $requirement->execute(permission: $permission);
    }
}
