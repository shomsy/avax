<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\Access;

use Avax\Auth\System\Capability\Access\RequireAuthentication\RequireAuthentication;
use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\Access\RequirePermission\PermissionDenied;
use Avax\Auth\System\Capability\Access\RequirePermission\RequirePermission;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserPermission;
use Avax\Auth\System\Flow\CheckAuthentication\CheckAuthentication;
use Avax\Auth\System\Flow\ReadCurrentUser\ReadCurrentUser;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Access capability boundaries.
 */
class AccessBoundaryTest extends TestCase
{
    /**
     * @throws \Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated
     */
    public function testRequireAuthenticationSuccess() : void
    {
        $check = Mockery::mock(CheckAuthentication::class);
        $check->shouldReceive('execute')->andReturn(true);

        $boundary = new RequireAuthentication(checkAuthentication: $check);
        $boundary->execute();
        $this->assertTrue(condition: true); // No exception thrown
    }

    public function testRequireAuthenticationFailure() : void
    {
        $check = Mockery::mock(CheckAuthentication::class);
        $check->shouldReceive('execute')->andReturn(false);

        $boundary = new RequireAuthentication(checkAuthentication: $check);

        $this->expectException(exception: Unauthenticated::class);
        $boundary->execute();
    }

    /**
     * @throws \Avax\Auth\System\Capability\Access\RequirePermission\PermissionDenied
     * @throws Unauthenticated
     */
    public function testRequirePermissionSuccess() : void
    {
        $permission = new UserPermission(value: 'write');
        $user       = Mockery::mock(User::class);
        $user->shouldReceive('hasPermission')->with(Mockery::on(fn ($p) => $p->value === 'write'))->andReturn(true);

        $readCurrentUser = Mockery::mock(ReadCurrentUser::class);
        $readCurrentUser->shouldReceive('execute')->andReturn($user);

        $boundary = new RequirePermission(readCurrentUser: $readCurrentUser);
        $boundary->execute(permission: $permission);
        $this->assertTrue(condition: true);
    }

    /**
     * @throws \Avax\Auth\System\Capability\Access\RequirePermission\PermissionDenied
     */
    public function testRequirePermissionFailureUserNotLoggedIn() : void
    {
        $readCurrentUser = Mockery::mock(ReadCurrentUser::class);
        $readCurrentUser->shouldReceive('execute')->andReturn(null);

        $boundary = new RequirePermission(readCurrentUser: $readCurrentUser);

        $this->expectException(exception: Unauthenticated::class);
        $boundary->execute(permission: new UserPermission(value: 'any'));
    }

    /**
     * @throws Unauthenticated
     */
    public function testRequirePermissionFailurePermissionMissing() : void
    {
        $permission = new UserPermission(value: 'write');
        $user       = Mockery::mock(User::class);
        $user->shouldReceive('hasPermission')->with(Mockery::on(fn ($p) => $p->value === 'write'))->andReturn(false);

        $readCurrentUser = Mockery::mock(ReadCurrentUser::class);
        $readCurrentUser->shouldReceive('execute')->andReturn($user);

        $boundary = new RequirePermission(readCurrentUser: $readCurrentUser);

        $this->expectException(exception: PermissionDenied::class);
        $boundary->execute(permission: $permission);
    }

    protected function tearDown() : void
    {
        Mockery::close();
    }
}
