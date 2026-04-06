<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capabilities\Access;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Capabilities\Access\RequireAuthentication\RequireAuthentication;
use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\Access\RequirePermission\RequirePermission;
use Avax\Auth\System\Capabilities\Access\RequirePermission\PermissionDenied;
use Avax\Auth\System\Capabilities\User\User;
use Avax\Auth\System\Capabilities\User\UserPermission;
use Avax\Auth\System\Flows\CheckAuthentication\CheckAuthentication;
use Avax\Auth\System\Flows\ReadCurrentUser\ReadCurrentUser;
use Mockery;

/**
 * Unit test for Access capability boundaries.
 */
class AccessBoundaryTest extends TestCase
{
    protected function tearDown() : void
    {
        Mockery::close();
    }

    /**
     * @throws \Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated
     */
    public function testRequireAuthenticationSuccess() : void
    {
        $check = Mockery::mock(CheckAuthentication::class);
        $check->shouldReceive('execute')->andReturn(true);

        $boundary = new RequireAuthentication(checkAuthentication: $check);
        $boundary->execute();
        $this->assertTrue(true); // No exception thrown
    }

    public function testRequireAuthenticationFailure() : void
    {
        $check = Mockery::mock(CheckAuthentication::class);
        $check->shouldReceive('execute')->andReturn(false);

        $boundary = new RequireAuthentication(checkAuthentication: $check);

        $this->expectException(Unauthenticated::class);
        $boundary->execute();
    }

    /**
     * @throws \Avax\Auth\System\Capabilities\Access\RequirePermission\PermissionDenied
     */
    public function testRequirePermissionSuccess() : void
    {
        $permission = new UserPermission('write');
        $user = Mockery::mock(User::class);
        $user->shouldReceive('hasPermission')->with(Mockery::on(fn($p) => $p->value === 'write'))->andReturn(true);

        $readCurrentUser = Mockery::mock(ReadCurrentUser::class);
        $readCurrentUser->shouldReceive('execute')->andReturn($user);

        $boundary = new RequirePermission(readCurrentUser: $readCurrentUser);
        $boundary->execute($permission);
        $this->assertTrue(true);
    }

    /**
     * @throws \Avax\Auth\System\Capabilities\Access\RequirePermission\PermissionDenied
     */
    public function testRequirePermissionFailureUserNotLoggedIn() : void
    {
        $readCurrentUser = Mockery::mock(ReadCurrentUser::class);
        $readCurrentUser->shouldReceive('execute')->andReturn(null);

        $boundary = new RequirePermission(readCurrentUser: $readCurrentUser);

        $this->expectException(Unauthenticated::class);
        $boundary->execute(new UserPermission('any'));
    }

    public function testRequirePermissionFailurePermissionMissing() : void
    {
        $permission = new UserPermission('write');
        $user = Mockery::mock(User::class);
        $user->shouldReceive('hasPermission')->with(Mockery::on(fn($p) => $p->value === 'write'))->andReturn(false);

        $readCurrentUser = Mockery::mock(ReadCurrentUser::class);
        $readCurrentUser->shouldReceive('execute')->andReturn($user);

        $boundary = new RequirePermission(readCurrentUser: $readCurrentUser);

        $this->expectException(PermissionDenied::class);
        $boundary->execute($permission);
    }
}
