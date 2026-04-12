<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Login;

use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\Identity\IssuedAuthentication;
use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserEmail;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\UserSourceInterface;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Auth\System\Flow\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flow\Login\AuthenticationFailed;
use Avax\Auth\System\Flow\Login\Credentials;
use Avax\Auth\System\Flow\Login\Login;
use Avax\Auth\System\Flow\Login\RateLimit\InMemoryLoginRateLimitStorage;
use Avax\Auth\System\Flow\Login\RateLimit\LoginRateLimit;
use Avax\Auth\System\Flow\Login\RateLimit\RateLimitException;
use Avax\Auth\System\Flow\Mfa\Challenge\InMemoryMfaChallengeStore;
use Avax\Auth\System\Flow\Mfa\Challenge\StartMfaChallenge;
use Avax\Auth\System\Flow\Mfa\InMemoryMfaStore;
use Avax\Auth\System\Flow\Mfa\MfaChallenge;
use Avax\Auth\System\Flow\Mfa\MfaChallengePurpose;
use Avax\Auth\System\Flow\Mfa\MfaMethod;
use Avax\Auth\System\Flow\Mfa\MfaMethodRecord;
use Avax\Auth\System\Flow\Token\IssuedRefreshToken;
use Avax\Auth\System\Flow\Token\IssuedToken;
use Avax\Auth\System\Flow\Verify\InMemoryEmailVerificationStateStore;
use Avax\Auth\Tests\Support\FrozenClock;
use Avax\Auth\System\Foundation\Clock;
use DateTimeImmutable;
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
        $passwordHasher = $this->passwordHasher();
        $user           = $this->userWithPassword(
            passwordHasher: $passwordHasher,
            userId        : $userId,
            password      : 'password'
        );

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('findByCredentials')
            ->once()
            ->with($credentials)
            ->andReturn($user);

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
        $identity->shouldReceive('sessionIdentity')->andReturn(null);

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
            startMfaChallenge       : $this->startMfaChallenge(new InMemoryMfaStore())
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

        $identity       = Mockery::mock(IdentityInterface::class);
        $identity->shouldNotReceive('issue');
        $identity->shouldReceive('sessionIdentity')->andReturn(null);

        $rateLimitStorage = new InMemoryLoginRateLimitStorage();
        $rateLimit        = new LoginRateLimit(
            storage: $rateLimitStorage,
            clock  : new Clock()
        );

        $login = new Login(
            userSource              : $userSource,
            passwordHasher          : $this->passwordHasher(),
            identity                : $identity,
            projectAuthenticatedUser: new ProjectAuthenticatedUser(
                                          emailVerificationState: new InMemoryEmailVerificationStateStore(),
                                          mfaStore              : new InMemoryMfaStore()
                                      ),
            currentAuthentication   : new CurrentAuthentication(),
            auditLog                : new InMemoryAuditLog(),
            mfaStore                : new InMemoryMfaStore(),
            startMfaChallenge       : $this->startMfaChallenge(new InMemoryMfaStore()),
            rateLimit               : $rateLimit
        );

        try {
            $login->execute(credentials: $credentials);
            self::fail('AuthenticationFailed was not raised.');
        } catch (AuthenticationFailed $exception) {
            $this->assertSame('Invalid credentials.', $exception->getMessage());
            $this->assertSame(1, $rateLimitStorage->get('user@example.com'));
        }
    }

    public function testLoginFailedWithInactiveUser() : void
    {
        $credentials = new Credentials(identifier: 'user@example.com', password: 'password');
        $passwordHasher = $this->passwordHasher();
        $user           = $this->userWithPassword(
            passwordHasher: $passwordHasher,
            userId        : new UserId(1),
            password      : 'password',
            isActive      : false
        );

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('findByCredentials')
            ->once()
            ->andReturn($user);

        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldNotReceive('issue');
        $identity->shouldReceive('sessionIdentity')->andReturn(null);

        $rateLimitStorage = new InMemoryLoginRateLimitStorage();
        $rateLimit        = new LoginRateLimit(
            storage: $rateLimitStorage,
            clock  : new Clock()
        );

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
            startMfaChallenge       : $this->startMfaChallenge(new InMemoryMfaStore()),
            rateLimit               : $rateLimit
        );

        try {
            $login->execute(credentials: $credentials);
            self::fail('AuthenticationFailed was not raised.');
        } catch (AuthenticationFailed $exception) {
            $this->assertSame('Invalid credentials.', $exception->getMessage());
            $this->assertSame(1, $rateLimitStorage->get('user@example.com'));
        }
    }

    public function testLoginFailedDueToRateLimiting() : void
    {
        $credentials = new Credentials(identifier: 'user@example.com', password: 'password');

        $userSource     = Mockery::mock(UserSourceInterface::class);
        $identity       = Mockery::mock(IdentityInterface::class);
        $identity->shouldNotReceive('issue');
        $identity->shouldReceive('sessionIdentity')->andReturn(null);

        $rateLimitStorage = new InMemoryLoginRateLimitStorage();
        $rateLimitStorage->increment('user@example.com');
        $rateLimit = new LoginRateLimit(
            storage     : $rateLimitStorage,
            clock       : new FrozenClock(new DateTimeImmutable()),
            maxAttempts : 1,
            decaySeconds: 60
        );

        $login = new Login(
            userSource              : $userSource,
            passwordHasher          : $this->passwordHasher(),
            identity                : $identity,
            projectAuthenticatedUser: new ProjectAuthenticatedUser(
                                          emailVerificationState: new InMemoryEmailVerificationStateStore(),
                                          mfaStore              : new InMemoryMfaStore()
                                      ),
            currentAuthentication   : new CurrentAuthentication(),
            auditLog                : new InMemoryAuditLog(),
            mfaStore                : new InMemoryMfaStore(),
            startMfaChallenge       : $this->startMfaChallenge(new InMemoryMfaStore()),
            rateLimit               : $rateLimit
        );

        $this->expectException(exception: RateLimitException::class);
        $this->expectExceptionMessage(message: 'Too many login attempts.');

        $login->execute(credentials: $credentials);
    }

    public function testLoginReturnsMfaChallengeWhenMfaIsEnabled() : void
    {
        $credentials = new Credentials(identifier: 'user@example.com', password: 'password');
        $userId      = new UserId(1);
        $passwordHasher = $this->passwordHasher();
        $user           = $this->userWithPassword(
            passwordHasher: $passwordHasher,
            userId        : $userId,
            password      : 'password'
        );

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('findByCredentials')->andReturn($user);

        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldNotReceive('issue');
        $identity->shouldReceive('sessionIdentity')->andReturn(null);

        $mfaStore = new InMemoryMfaStore();
        $mfaStore->saveMethod(new MfaMethodRecord(
            userId   : $userId,
            method   : MfaMethod::TOTP,
            secret   : 'SECRET',
            enabledAt: new DateTimeImmutable('-1 minute')
        ));
        $startMfaChallenge = $this->startMfaChallenge($mfaStore);

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
        $this->assertNotNull($result->mfaChallengeId());
        $this->assertSame(MfaChallengePurpose::LOGIN, $result->mfaChallenge()?->purpose);
    }

    public function testLoginRehashesStoredPasswordWhenHasherPolicyChanged() : void
    {
        $credentials = new Credentials(identifier: 'user@example.com', password: 'password');
        $userId      = new UserId(1);
        $legacyHasher = new PasswordHasher(algo: PASSWORD_BCRYPT, options: ['cost' => 4]);
        $user         = $this->userWithPassword(
            passwordHasher: $legacyHasher,
            userId        : $userId,
            password      : 'password'
        );
        $currentHasher = new PasswordHasher(algo: PASSWORD_BCRYPT, options: ['cost' => 12]);

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('findByCredentials')->once()->with($credentials)->andReturn($user);
        $userSource->shouldReceive('updatePassword')->once()->with(
            $userId,
            Mockery::on(static fn (string $hash) : bool => password_verify('password', $hash))
        );

        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('issue')->once()->with($user)->andReturn(new IssuedAuthentication(
            mode: AuthenticationMode::TOKEN,
            accessToken: new IssuedToken(
                token    : 'token-123',
                tokenId  : 'access-123',
                expiresAt: new DateTimeImmutable('+1 hour')
            )
        ));
        $identity->shouldReceive('sessionIdentity')->andReturn(null);

        $login = new Login(
            userSource              : $userSource,
            passwordHasher          : $currentHasher,
            identity                : $identity,
            projectAuthenticatedUser: new ProjectAuthenticatedUser(
                emailVerificationState: new InMemoryEmailVerificationStateStore(),
                mfaStore              : new InMemoryMfaStore()
            ),
            currentAuthentication   : new CurrentAuthentication(),
            auditLog                : new InMemoryAuditLog(),
            mfaStore                : new InMemoryMfaStore(),
            startMfaChallenge       : $this->startMfaChallenge(new InMemoryMfaStore())
        );

        $result = $login->execute($credentials);

        $this->assertTrue($result->isAuthenticated());
    }

    protected function tearDown() : void
    {
        Mockery::close();
    }

    private function passwordHasher() : PasswordHasher
    {
        return new PasswordHasher(algo: PASSWORD_BCRYPT, options: ['cost' => 4]);
    }

    private function userWithPassword(
        PasswordHasher $passwordHasher,
        UserId $userId,
        string $password,
        bool $isActive = true
    ) : User {
        return User::create(
            id          : $userId,
            email       : new UserEmail('user@example.com'),
            username    : 'user',
            passwordHash: $passwordHasher->hash($password),
            roles       : [],
            permissions : [],
            isActive    : $isActive
        );
    }

    private function startMfaChallenge(InMemoryMfaStore $mfaStore) : StartMfaChallenge
    {
        return new StartMfaChallenge(
            currentAuthentication: new CurrentAuthentication(),
            mfaStore             : $mfaStore,
            challengeStore       : new InMemoryMfaChallengeStore(),
            auditLog             : new InMemoryAuditLog(),
            clock                : new Clock()
        );
    }
}
