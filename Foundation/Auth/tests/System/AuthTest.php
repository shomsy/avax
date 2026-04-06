<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\System;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Auth;
use Avax\Auth\System\Flows\Login\Login;
use Avax\Auth\System\Flows\Login\Credentials;
use Avax\Auth\System\Flows\Logout\Logout;
use Avax\Auth\System\Flows\CheckAuthentication\CheckAuthentication;
use Avax\Auth\System\Flows\ReadCurrentUser\ReadCurrentUser;
use Avax\Auth\System\Flows\ChangePassword\ChangePassword;
use Avax\Auth\System\Flows\ChangePassword\ChangePasswordData;
use Avax\Auth\System\Flows\Register\Register;
use Avax\Auth\System\Flows\Register\RegistrationData;
use Avax\Auth\System\Capabilities\User\User;
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
        $changePasswordFlow = Mockery::mock(ChangePassword::class);
        $registerFlow = Mockery::mock(Register::class);

        $auth = new Auth(
            login: $loginFlow,
            logout: $logoutFlow,
            checkAuthentication: $checkFlow,
            readCurrentUser: $readUserFlow,
            changePassword: $changePasswordFlow,
            register: $registerFlow
        );

        $user = Mockery::mock(User::class);
        $credentials = new Credentials('user', 'pass');
        $loginFlow->shouldReceive('execute')->with($credentials)->andReturn($user);
        $this->assertSame($user, $auth->login($credentials));

        $logoutFlow->shouldReceive('execute')->once();
        $auth->logout();

        $checkFlow->shouldReceive('execute')->andReturn(true);
        $this->assertTrue($auth->check());

        $readUserFlow->shouldReceive('execute')->andReturn($user);
        $this->assertSame($user, $auth->user());

        $cpData = new ChangePasswordData('old', 'new');
        $changePasswordFlow->shouldReceive('execute')->with($user, $cpData)->once();
        $auth->changePassword($user, $cpData);

        $regData = new RegistrationData('email', 'nick', 'pass');
        $registerFlow->shouldReceive('execute')->with($regData)->andReturn($user);
        $this->assertSame($user, $auth->register($regData));
    }
}
