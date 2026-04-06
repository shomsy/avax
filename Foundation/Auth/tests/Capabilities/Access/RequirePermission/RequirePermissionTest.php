<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capabilities\Access\RequirePermission;

use PHPUnit\Framework\TestCase;
use Avax\Auth\Capabilities\Access\RequirePermission\RequirePermission;
use Avax\Auth\Capabilities\Access\RequirePermission\PermissionDenied;
use Avax\Auth\Flows\ReadCurrentUser\ReadCurrentUser;
use Avax\Auth\Capabilities\User\User;
use Avax\Auth\Capabilities\User\UserPermission;
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
        $permission = new UserPermission('delete_user');
        $user = Mockery::mock(User::class);
        $user->shouldReceive('hasPermission')
            ->with($permission)
            ->andReturn(true);

        $readCurrentUser = Mockery::mock(ReadCurrentUser::class);
        $readCurrentUser->shouldReceive('execute')->andReturn($user);

        $requirement = new RequirePermission(readCurrentUser: $readCurrentUser);
        $requirement->execute(permission: $permission);

        $this->assertTrue(true); // No exception thrown
    }

    public function testRequirePermissionFailure() : void
    {
        $permission = new UserPermission('delete_user');
        $user = Mockery::mock(User::class);
        $user->shouldReceive('hasPermission')
            ->with($permission)
            ->andReturn(false);

        $readCurrentUser = Mockery::mock(ReadCurrentUser::class);
        $readCurrentUser->shouldReceive('execute')->andReturn($user);

        $requirement = new RequirePermission(readCurrentUser: $readCurrentUser);

        $this->expectException(PermissionDenied::class);

        $requirement->execute(permission: $permission);
    }
}
