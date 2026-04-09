<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Integration;

use Avax\Auth\System\Auth;
use Avax\Auth\System\Capability\Identity\Identity;
use Avax\Auth\System\Capability\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flow\ChangePassword\ChangePasswordData;
use Avax\Auth\System\Flow\Login\Credentials;
use Avax\Auth\System\Flow\Register\RegistrationData;
use Exception;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Integration test covering the full user lifecycle.
 *
 * Banal: Testing the whole system together.
 */
class AuthLifecycleTest extends TestCase
{
    private Auth                 $auth;
    private InMemoryUserSource   $userSource;
    private JwtIdentityInterface $jwtIdentity;

    /**
     * @throws Exception
     */
    public function testFullUserLifecyclePositive() : void
    {
        // 1. Register
        $regData = new RegistrationData(
            email   : 'integration@test.com',
            username: 'itest',
            password: 'initial-password'
        );
        $user    = $this->auth->register(data: $regData);
        $this->assertEquals(expected: 'integration@test.com', actual: $user->getEmail()->value);

        // 2. Login
        $credentials = new Credentials(identifier: 'integration@test.com', password: 'initial-password');
        $this->jwtIdentity->shouldReceive('issue')->once()->with($user);
        $loggedInUser = $this->auth->login(credentials: $credentials);
        $this->assertEquals(expected: $user->getId()->value, actual: $loggedInUser->getId()->value);

        // 3. Change Password
        $cpData = new ChangePasswordData(
            currentPassword: 'initial-password',
            newPassword    : 'new-secure-password'
        );
        $this->auth->changePassword(user: $loggedInUser, data: $cpData);

        // 4. Login with NEW password
        $newCredentials = new Credentials(identifier: 'itest', password: 'new-secure-password');
        $this->jwtIdentity->shouldReceive('issue')->once()->with($loggedInUser);
        $reLoggedInUser = $this->auth->login(credentials: $newCredentials);
        $this->assertEquals(expected: $user->getId()->value, actual: $reLoggedInUser->getId()->value);

        // 5. Logout
        $this->auth->logout();
        $this->assertTrue(condition: true);
    }

    /**
     * @throws Exception
     */
    public function testUserLifecycleNegative() : void
    {
        // 1. Register user
        $this->auth->register(data: new RegistrationData(email: 'fail@test.com', username: 'fail', password: 'pass'));

        // 2. Try to register with SAME email (Negative)
        $this->expectException(exception: Exception::class);
        $this->expectExceptionMessage(message: 'Email is already taken.');
        $this->auth->register(data: new RegistrationData(email: 'fail@test.com', username: 'other', password: 'pass'));
    }

    /**
     * @throws Exception
     */
    public function testLoginNegative() : void
    {
        // 1. Register user
        $this->auth->register(data: new RegistrationData(email: 'login@fail.com', username: 'loginfail', password: 'correct'));

        // 2. Try login with WRONG password (Negative)
        $this->expectException(exception: Exception::class);
        $this->expectExceptionMessage(message: 'Invalid credentials.');
        $this->auth->login(credentials: new Credentials(identifier: 'login@fail.com', password: 'WRONG'));
    }

    protected function setUp() : void
    {
        $this->userSource  = new InMemoryUserSource();
        $this->jwtIdentity = Mockery::mock(JwtIdentityInterface::class);
        $identity          = new Identity(jwtIdentity: $this->jwtIdentity);

        $this->auth = Auth::configuration()
            ->forUser(userSource: $this->userSource)
            ->withIdentity(identity: $identity)
            ->ready();
    }

    protected function tearDown() : void
    {
        Mockery::close();
    }
}
