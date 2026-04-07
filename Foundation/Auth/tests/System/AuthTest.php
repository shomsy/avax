<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\System;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Auth;
use Avax\Auth\System\Flow\Login\Login;
use Avax\Auth\System\Flow\Login\Credentials;
use Avax\Auth\System\Flow\Logout\Logout;
use Avax\Auth\System\Capability\Access\AccessInterface;
use Avax\Auth\System\Flow\CheckAuthentication\CheckAuthentication;
use Avax\Auth\System\Flow\ReadCurrentUser\ReadCurrentUser;
use Avax\Auth\System\Flow\ChangePassword\ChangePassword;
use Avax\Auth\System\Flow\ChangePassword\ChangePasswordData;
use Avax\Auth\System\Flow\Register\Register;
use Avax\Auth\System\Flow\Register\RegistrationData;
use Avax\Auth\System\Capability\User\User;
use Mockery;

/**
 * Unit test for Auth Facade.
 */
class AuthTest extends TestCase
{
    protected function tearDown() : void
    {
        Mockery::close();
    }

    public function testAuthFacadeDelegatesToFlows() : void
    {
        $loginFlow = Mockery::mock(Login::class);
        $logoutFlow = Mockery::mock(Logout::class);
        $checkFlow = Mockery::mock(CheckAuthentication::class);
        $readUserFlow = Mockery::mock(ReadCurrentUser::class);
        $access = Mockery::mock(AccessInterface::class);
        $changePasswordFlow = Mockery::mock(ChangePassword::class);
        $registerFlow = Mockery::mock(Register::class);

        $auth = new Auth(
            login: $loginFlow,
            logout: $logoutFlow,
            checkAuthentication: $checkFlow,
            readCurrentUser: $readUserFlow,
            access: $access,
            changePassword: $changePasswordFlow,
            register: $registerFlow
        );

        $user = Mockery::mock(User::class);
        $credentials = new Credentials(identifier: 'user', password: 'pass');
        $loginFlow->shouldReceive('execute')->with($credentials)->andReturn($user);
        $this->assertSame(expected: $user, actual: $auth->login(credentials: $credentials));

        $logoutFlow->shouldReceive('execute')->once();
        $auth->logout();

        $checkFlow->shouldReceive('execute')->andReturn(true);
        $this->assertTrue(condition: $auth->check());

        $readUserFlow->shouldReceive('execute')->andReturn($user);
        $this->assertSame(expected: $user, actual: $auth->user());

        $this->assertSame(expected: $access, actual: $auth->access());

        $cpData = new ChangePasswordData(currentPassword: 'old', newPassword: 'new');
        $changePasswordFlow->shouldReceive('execute')->with($user, $cpData)->once();
        $auth->changePassword(user: $user, data: $cpData);

        $regData = new RegistrationData(email: 'email', username: 'nick', password: 'pass');
        $registerFlow->shouldReceive('execute')->with($regData)->andReturn($user);
        $this->assertSame(expected: $user, actual: $auth->register(data: $regData));
    }
}
