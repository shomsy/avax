<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\Login;

use PHPUnit\Framework\TestCase;
use Avax\Auth\System\Flows\Login\Login;
use Avax\Auth\System\Flows\Login\Credentials;
use Avax\Auth\System\Flows\Login\RateLimit\LoginRateLimit;
use Avax\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Auth\System\Capabilities\User\User;
use Avax\Auth\System\Capabilities\User\UserId;
use Avax\Auth\System\Capabilities\UserSource\UserSourceInterface;
use Avax\Auth\System\Capabilities\PasswordHashing\PasswordHasher;
use Mockery;
use Exception;

/**
 * Unit test for Login flow.
 *
 * Banal: Testing the login process.
 */
class LoginTest extends TestCase
{
    protected function tearDown() : void
    {
        Mockery::close();
    }

    /**
     * @throws \Exception
     */
    public function testLoginSuccess() : void
    {
        $credentials = new Credentials(identifier: 'user@example.com', password: 'password');
        $userId = new UserId(1);
        $user = Mockery::mock(User::class);
        $user->shouldReceive('isActive')->andReturn(true);
        $user->shouldReceive('getPasswordHash')->andReturn('hashed_password');
        $user->shouldReceive('getId')->andReturn($userId);

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('findByCredentials')
            ->once()
            ->with($credentials)
            ->andReturn($user);

        $passwordHasher = Mockery::mock(PasswordHasher::class);
        $passwordHasher->shouldReceive('verify')
            ->once()
            ->with('password', 'hashed_password')
            ->andReturn(true);

        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('issue')->once()->with($user)->andReturn('token-123');

        $rateLimit = Mockery::mock(LoginRateLimit::class);
        $rateLimit->shouldReceive('check')->once();
        $rateLimit->shouldReceive('reset')->once();

        $login = new Login(
            userSource: $userSource,
            passwordHasher: $passwordHasher,
            identity: $identity,
            rateLimit: $rateLimit
        );
        $result = $login->execute(credentials: $credentials);

        $this->assertSame($user, $result);
    }

    public function testLoginFailedWithInvalidCredentials() : void
    {
        $credentials = new Credentials(identifier: 'user@example.com', password: 'wrong_password');

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('findByCredentials')
            ->once()
            ->andReturn(null);

        $passwordHasher = Mockery::mock(PasswordHasher::class);
        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldNotReceive('issue');

        $rateLimit = Mockery::mock(LoginRateLimit::class);
        $rateLimit->shouldReceive('check')->once();
        $rateLimit->shouldReceive('recordFailed')->once();

        $login = new Login(
            userSource: $userSource,
            passwordHasher: $passwordHasher,
            identity: $identity,
            rateLimit: $rateLimit
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid credentials.');

        $login->execute(credentials: $credentials);
    }

    public function testLoginFailedWithInactiveUser() : void
    {
        $credentials = new Credentials(identifier: 'user@example.com', password: 'password');
        $user = Mockery::mock(User::class);
        $user->shouldReceive('isActive')->andReturn(false);
        $user->shouldReceive('getPasswordHash')->andReturn('hashed_password');

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('findByCredentials')
            ->once()
            ->andReturn($user);

        $passwordHasher = Mockery::mock(PasswordHasher::class);
        $passwordHasher->shouldReceive('verify')
            ->once()
            ->andReturn(true);

        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldNotReceive('issue');

        $rateLimit = Mockery::mock(LoginRateLimit::class);
        $rateLimit->shouldReceive('check')->once();
        $rateLimit->shouldReceive('recordFailed')->once();

        $login = new Login(
            userSource: $userSource,
            passwordHasher: $passwordHasher,
            identity: $identity,
            rateLimit: $rateLimit
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid credentials.');

        $login->execute(credentials: $credentials);
    }

    public function testLoginFailedDueToRateLimiting() : void
    {
        $credentials = new Credentials(identifier: 'user@example.com', password: 'password');

        $userSource = Mockery::mock(UserSourceInterface::class);
        $passwordHasher = Mockery::mock(PasswordHasher::class);
        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldNotReceive('issue');

        $rateLimit = Mockery::mock(LoginRateLimit::class);
        $rateLimit->shouldReceive('check')
            ->once()
            ->andThrow(new Exception('Too many login attempts.'));

        $login = new Login(
            userSource: $userSource,
            passwordHasher: $passwordHasher,
            identity: $identity,
            rateLimit: $rateLimit
        );

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Too many login attempts.');

        $login->execute(credentials: $credentials);
    }
}
