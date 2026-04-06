<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Auth;
use Avax\Auth\System\Capabilities\UserSource\InMemoryUserSource;
use Avax\Auth\System\Capabilities\Identity\Identity;
use Avax\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capabilities\User\User;
use Avax\Auth\System\Flows\Login\Credentials;
use Avax\Auth\System\Flows\Register\RegistrationData;
use Avax\Auth\System\Flows\ChangePassword\ChangePasswordData;
use Mockery;
use Exception;

/**
 * Integration test covering the full user lifecycle.
 * 
 * Banal: Testing the whole system together.
 */
class AuthLifecycleTest extends TestCase
{
    private Auth $auth;
    private InMemoryUserSource $userSource;
    private JwtIdentityInterface $jwtIdentity;

    protected function setUp() : void
    {
        $this->userSource = new InMemoryUserSource();
        $this->jwtIdentity = Mockery::mock(JwtIdentityInterface::class);
        $identity = new Identity(jwtIdentity: $this->jwtIdentity);

        $this->auth = Auth::configuration()
            ->forUser($this->userSource)
            ->withIdentity($identity)
            ->ready();
    }

    protected function tearDown() : void
    {
        Mockery::close();
    }

    public function testFullUserLifecyclePositive() : void
    {
        // 1. Register
        $regData = new RegistrationData(
            email: 'integration@test.com',
            username: 'itest',
            password: 'initial-password'
        );
        $user = $this->auth->register($regData);
        $this->assertEquals('integration@test.com', $user->getEmail()->value);

        // 2. Login
        $credentials = new Credentials(identifier: 'integration@test.com', password: 'initial-password');
        $this->jwtIdentity->shouldReceive('issue')->once()->with($user);
        $loggedInUser = $this->auth->login($credentials);
        $this->assertEquals($user->getId()->value, $loggedInUser->getId()->value);

        // 3. Change Password
        $cpData = new ChangePasswordData(
            currentPassword: 'initial-password',
            newPassword: 'new-secure-password'
        );
        $this->auth->changePassword($loggedInUser, $cpData);

        // 4. Login with NEW password
        $newCredentials = new Credentials(identifier: 'itest', password: 'new-secure-password');
        $this->jwtIdentity->shouldReceive('issue')->once()->with($loggedInUser);
        $reLoggedInUser = $this->auth->login($newCredentials);
        $this->assertEquals($user->getId()->value, $reLoggedInUser->getId()->value);

        // 5. Logout
        $this->auth->logout();
        $this->assertTrue(true);
    }

    public function testUserLifecycleNegative() : void
    {
        // 1. Register user
        $this->auth->register(new RegistrationData('fail@test.com', 'fail', 'pass'));

        // 2. Try to register with SAME email (Negative)
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Email is already taken.');
        $this->auth->register(new RegistrationData('fail@test.com', 'other', 'pass'));
    }

    public function testLoginNegative() : void
    {
        // 1. Register user
        $this->auth->register(new RegistrationData('login@fail.com', 'loginfail', 'correct'));

        // 2. Try login with WRONG password (Negative)
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid credentials.');
        $this->auth->login(new Credentials('login@fail.com', 'WRONG'));
    }
}
