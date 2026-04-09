<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Login;

use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\Identity\IssuedAuthentication;
use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Auth\System\Flow\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flow\Login\AuthenticationFailed;
use Avax\Auth\System\Flow\Login\Credentials;
use Avax\Auth\System\Flow\Login\Login;
use Avax\Auth\System\Flow\Login\RateLimit\LoginRateLimit;
use Avax\Auth\System\Flow\Mfa\Challenge\StartMfaChallenge;
use Avax\Auth\System\Flow\Mfa\InMemoryMfaStore;
use Avax\Auth\System\Flow\Mfa\MfaChallenge;
use Avax\Auth\System\Flow\Mfa\MfaChallengePurpose;
use Avax\Auth\System\Flow\Token\IssuedRefreshToken;
use Avax\Auth\System\Flow\Token\IssuedToken;
use Avax\Auth\System\Flow\Verify\InMemoryEmailVerificationStateStore;
use DateTimeImmutable;
use Exception;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for Login flow.
 *
 * Banal: Testing the login process.
 */
class LoginTest extends TestCase
{
    /**
     * @throws \Exception
     */
    public function testLoginSuccess() : void
    {
        $credentials = new Credentials(identifier: 'user@example.com', password: 'password');
        $userId      = new UserId(value: 1);
        $user        = Mockery::mock(User::class);
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
        $identity->shouldReceive('issue')->once()->with($user)->andReturn(new IssuedAuthentication(
                                                                              mode        : AuthenticationMode::TOKEN,
                                                                              accessToken : new IssuedToken(
                                                                                                token    : 'token-123',
                                                                                                tokenId  : 'access-123',
                                                                                                expiresAt: new DateTimeImmutable('+1 hour')
                                                                                            ),
                                                                              refreshToken: new IssuedRefreshToken(
                                                                                                token    : 'refresh-123',
                                                                                                tokenId  : 'refresh-123',
                                                                                                familyId : 'family-123',
                                                                                                userId   : $userId,
                                                                                                expiresAt: new DateTimeImmutable('+30 days')
                                                                                            )
                                                                          ));

        $rateLimit = Mockery::mock(LoginRateLimit::class);
        $rateLimit->shouldReceive('check')->once();
        $rateLimit->shouldReceive('reset')->once();
        $startMfaChallenge = Mockery::mock(StartMfaChallenge::class);
        $startMfaChallenge->shouldNotReceive('issueForLogin');

        $login  = new Login(
            userSource              : $userSource,
            passwordHasher          : $passwordHasher,
            identity                : $identity,
            projectAuthenticatedUser: new ProjectAuthenticatedUser(
                                          emailVerificationState: new InMemoryEmailVerificationStateStore(),
                                          mfaStore              : new InMemoryMfaStore()
                                      ),
            currentAuthentication   : new CurrentAuthentication(),
            auditLog                : new InMemoryAuditLog(),
            mfaStore                : new InMemoryMfaStore(),
            startMfaChallenge       : $startMfaChallenge,
            rateLimit               : $rateLimit
        );
        $result = $login->execute(credentials: $credentials);

        $this->assertTrue($result->isAuthenticated());
        $this->assertSame('token-123', $result->accessToken());
        $this->assertSame('refresh-123', $result->refreshToken());
        $this->assertSame(1, $result->user()?->id);
    }

    public function testLoginFailedWithInvalidCredentials() : void
    {
        $credentials = new Credentials(identifier: 'user@example.com', password: 'wrong_password');

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('findByCredentials')
            ->once()
            ->andReturn(null);

        $passwordHasher = Mockery::mock(PasswordHasher::class);
        $identity       = Mockery::mock(IdentityInterface::class);
        $identity->shouldNotReceive('issue');

        $rateLimit = Mockery::mock(LoginRateLimit::class);
        $rateLimit->shouldReceive('check')->once();
        $rateLimit->shouldReceive('recordFailed')->once();

        $login = new Login(
            userSource              : $userSource,
            passwordHasher          : $passwordHasher,
            identity                : $identity,
            projectAuthenticatedUser: new ProjectAuthenticatedUser(
                                          emailVerificationState: new InMemoryEmailVerificationStateStore(),
                                          mfaStore              : new InMemoryMfaStore()
                                      ),
            currentAuthentication   : new CurrentAuthentication(),
            auditLog                : new InMemoryAuditLog(),
            mfaStore                : new InMemoryMfaStore(),
            startMfaChallenge       : Mockery::mock(StartMfaChallenge::class),
            rateLimit               : $rateLimit
        );

        $this->expectException(exception: AuthenticationFailed::class);
        $this->expectExceptionMessage(message: 'Invalid credentials.');

        $login->execute(credentials: $credentials);
    }

    public function testLoginFailedWithInactiveUser() : void
    {
        $credentials = new Credentials(identifier: 'user@example.com', password: 'password');
        $user        = Mockery::mock(User::class);
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
            userSource              : $userSource,
            passwordHasher          : $passwordHasher,
            identity                : $identity,
            projectAuthenticatedUser: new ProjectAuthenticatedUser(
                                          emailVerificationState: new InMemoryEmailVerificationStateStore(),
                                          mfaStore              : new InMemoryMfaStore()
                                      ),
            currentAuthentication   : new CurrentAuthentication(),
            auditLog                : new InMemoryAuditLog(),
            mfaStore                : new InMemoryMfaStore(),
            startMfaChallenge       : Mockery::mock(StartMfaChallenge::class),
            rateLimit               : $rateLimit
        );

        $this->expectException(exception: AuthenticationFailed::class);
        $this->expectExceptionMessage(message: 'Invalid credentials.');

        $login->execute(credentials: $credentials);
    }

    public function testLoginFailedDueToRateLimiting() : void
    {
        $credentials = new Credentials(identifier: 'user@example.com', password: 'password');

        $userSource     = Mockery::mock(UserSourceInterface::class);
        $passwordHasher = Mockery::mock(PasswordHasher::class);
        $identity       = Mockery::mock(IdentityInterface::class);
        $identity->shouldNotReceive('issue');

        $rateLimit = Mockery::mock(LoginRateLimit::class);
        $rateLimit->shouldReceive('check')
            ->once()
            ->andThrow(new Exception(message: 'Too many login attempts.'));

        $login = new Login(
            userSource              : $userSource,
            passwordHasher          : $passwordHasher,
            identity                : $identity,
            projectAuthenticatedUser: new ProjectAuthenticatedUser(
                                          emailVerificationState: new InMemoryEmailVerificationStateStore(),
                                          mfaStore              : new InMemoryMfaStore()
                                      ),
            currentAuthentication   : new CurrentAuthentication(),
            auditLog                : new InMemoryAuditLog(),
            mfaStore                : new InMemoryMfaStore(),
            startMfaChallenge       : Mockery::mock(StartMfaChallenge::class),
            rateLimit               : $rateLimit
        );

        $this->expectException(exception: Exception::class);
        $this->expectExceptionMessage(message: 'Too many login attempts.');

        $login->execute(credentials: $credentials);
    }

    public function testLoginReturnsMfaChallengeWhenMfaIsEnabled() : void
    {
        $credentials = new Credentials(identifier: 'user@example.com', password: 'password');
        $userId      = new UserId(1);
        $user        = Mockery::mock(User::class);
        $user->shouldReceive('isActive')->andReturn(true);
        $user->shouldReceive('getPasswordHash')->andReturn('hashed_password');
        $user->shouldReceive('getId')->andReturn($userId);
        $user->shouldReceive('getEmail')->andReturn(new \Avax\Auth\System\Capability\User\UserEmail('user@example.com'));
        $user->shouldReceive('getUsername')->andReturn('user');
        $user->shouldReceive('getRoles')->andReturn([]);
        $user->shouldReceive('getPermissions')->andReturn([]);

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('findByCredentials')->andReturn($user);

        $passwordHasher = Mockery::mock(PasswordHasher::class);
        $passwordHasher->shouldReceive('verify')->andReturn(true);

        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldNotReceive('issue');

        $mfaStore = new InMemoryMfaStore();
        $mfaStore->saveMethod(new \Avax\Auth\System\Flow\Mfa\MfaMethodRecord(
                                  userId   : $userId,
                                  method   : \Avax\Auth\System\Flow\Mfa\MfaMethod::TOTP,
                                  secret   : 'SECRET',
                                  enabledAt: new DateTimeImmutable('-1 minute')
                              ));
        $challenge         = new MfaChallenge(
            challengeId      : 'challenge-1',
            purpose          : MfaChallengePurpose::LOGIN,
            expiresAt        : new DateTimeImmutable('+5 minutes'),
            remainingAttempts: 5
        );
        $startMfaChallenge = Mockery::mock(StartMfaChallenge::class);
        $startMfaChallenge->shouldReceive('issueForLogin')->once()->andReturn($challenge);

        $login = new Login(
            userSource              : $userSource,
            passwordHasher          : $passwordHasher,
            identity                : $identity,
            projectAuthenticatedUser: new ProjectAuthenticatedUser(
                                          emailVerificationState: new InMemoryEmailVerificationStateStore(),
                                          mfaStore              : $mfaStore
                                      ),
            currentAuthentication   : new CurrentAuthentication(),
            auditLog                : new InMemoryAuditLog(),
            mfaStore                : $mfaStore,
            startMfaChallenge       : $startMfaChallenge
        );

        $result = $login->execute($credentials);

        $this->assertTrue($result->requiresMfa());
        $this->assertSame('challenge-1', $result->mfaChallengeId());
    }

    protected function tearDown() : void
    {
        Mockery::close();
    }
}
