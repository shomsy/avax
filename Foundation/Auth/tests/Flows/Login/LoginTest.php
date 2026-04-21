<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\Login;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\InMemoryAuditLog;
use Avax\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Auth\System\Capabilities\Identity\IssuedAuthentication;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Challenge\InMemoryMfaChallengeStore;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Challenge\StartMfaChallenge;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enums\InMemoryMfaStore;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enums\MfaChallengePurpose;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enums\MfaMethod;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\InMemoryMfaStore;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaChallengePurpose;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaMethod;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\MfaMethodRecord;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Verify\InMemoryMfaChallengeStore;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Verify\StartMfaChallenge;
use Avax\Auth\System\Capabilities\Identity\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\IssuedRefreshToken;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\IssuedToken;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\IssuedRefreshToken;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\IssuedToken;
use Avax\Auth\System\Capabilities\Identity\User\User;
use Avax\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Auth\System\Flows\Login\AuthenticationFailed;
use Avax\Auth\System\Flows\Login\Credentials;
use Avax\Auth\System\Flows\Login\Login;
use Avax\Auth\System\Flows\Login\RateLimit\InMemoryLoginRateLimitStorage;
use Avax\Auth\System\Flows\Login\RateLimit\LoginRateLimit;
use Avax\Auth\System\Flows\Login\RateLimit\RateLimitException;
use Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\InMemoryEmailVerificationStateStore;
use Avax\Auth\System\Foundation\Clock;
use Avax\Auth\Tests\Support\FrozenClock;
use DateTimeImmutable;
use Exception;
use Mockery;
use Override;
use PHPUnit\Framework\TestCase;
use SensitiveParameter;

/**
 * Unit test for Login flow.
 *
 * Banal: Testing the login process.
 */
class LoginTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testLoginSuccess() : void
    {
        $credentials    = new Credentials(identifier: 'user@example.com', password: 'password');
        $userId         = new UserId(value: 1);
        $passwordHasher = $this->passwordHasher();
        $user           = $this->userWithPassword(
            passwordHasher: $passwordHasher,
            userId        : $userId
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
                                                                                                expiresAt: new DateTimeImmutable(datetime: '+1 hour')
                                                                                            ),
                                                                              refreshToken: new IssuedRefreshToken(
                                                                                                token    : 'refresh-123',
                                                                                                tokenId  : 'refresh-123',
                                                                                                familyId : 'family-123',
                                                                                                userId   : $userId,
                                                                                                expiresAt: new DateTimeImmutable(datetime: '+30 days')
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
            startMfaChallenge       : $this->startMfaChallenge(mfaStore: new InMemoryMfaStore()),
            clock                   : new Clock()
        );
        $result = $login->execute(credentials: $credentials);

        $this->assertTrue(condition: $result->isAuthenticated());
        $this->assertSame(expected: 'token-123', actual: $result->accessToken());
        $this->assertSame(expected: 'refresh-123', actual: $result->refreshToken());
        $this->assertSame(expected: 1, actual: $result->user()?->id);
    }

    /**
     * @throws Exception
     */
    public function testLoginUsesConfiguredClockForAuditEvents() : void
    {
        $credentials    = new Credentials(identifier: 'user@example.com', password: 'password');
        $userId         = new UserId(value: 1);
        $passwordHasher = $this->passwordHasher();
        $user           = $this->userWithPassword(
            passwordHasher: $passwordHasher,
            userId        : $userId
        );

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('findByCredentials')
            ->once()
            ->with($credentials)
            ->andReturn($user);

        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('issue')->once()->with($user)->andReturn(new IssuedAuthentication(
                                                                              mode       : AuthenticationMode::TOKEN,
                                                                              accessToken: new IssuedToken(
                                                                                               token    : 'token-123',
                                                                                               tokenId  : 'access-123',
                                                                                               expiresAt: new DateTimeImmutable(datetime: '+1 hour')
                                                                                           )
                                                                          ));
        $identity->shouldReceive('sessionIdentity')->andReturn(null);

        $auditLog = new InMemoryAuditLog();
        $clock    = new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-20T11:00:00+00:00'));

        $login = new Login(
            userSource              : $userSource,
            passwordHasher          : $passwordHasher,
            identity                : $identity,
            projectAuthenticatedUser: new ProjectAuthenticatedUser(
                                          emailVerificationState: new InMemoryEmailVerificationStateStore(),
                                          mfaStore              : new InMemoryMfaStore()
                                      ),
            currentAuthentication   : new CurrentAuthentication(),
            auditLog                : $auditLog,
            mfaStore                : new InMemoryMfaStore(),
            startMfaChallenge       : $this->startMfaChallenge(mfaStore: new InMemoryMfaStore()),
            clock                   : $clock
        );

        $login->execute(credentials: $credentials);

        $events = $auditLog->events();

        $this->assertCount(expectedCount: 1, haystack: $events);
        $this->assertSame(expected: 'auth.login.succeeded', actual: $events[0]->name);
        $this->assertEquals(expected: $clock->now(), actual: $events[0]->occurredAt);
    }

    private function passwordHasher() : PasswordHasher
    {
        return new PasswordHasher(algo: PASSWORD_BCRYPT, options: ['cost' => 4]);
    }

    private function userWithPassword(
        #[SensitiveParameter] PasswordHasher $passwordHasher,
        UserId                               $userId,
        bool                                 $isActive = true
    ) : User
    {
        return User::create(
            id          : $userId,
            email       : new UserEmail(value: 'user@example.com'),
            username    : 'user',
            passwordHash: $passwordHasher->hash(password: 'password'),
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

    /**
     * @throws RateLimitException
     */
    public function testLoginFailedWithInvalidCredentials() : void
    {
        $credentials = new Credentials(identifier: 'user@example.com', password: 'wrong_password');

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('findByCredentials')
            ->once()
            ->andReturn(null);

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
            passwordHasher          : $this->passwordHasher(),
            identity                : $identity,
            projectAuthenticatedUser: new ProjectAuthenticatedUser(
                                          emailVerificationState: new InMemoryEmailVerificationStateStore(),
                                          mfaStore              : new InMemoryMfaStore()
                                      ),
            currentAuthentication   : new CurrentAuthentication(),
            auditLog                : new InMemoryAuditLog(),
            mfaStore                : new InMemoryMfaStore(),
            startMfaChallenge       : $this->startMfaChallenge(mfaStore: new InMemoryMfaStore()),
            clock                   : new Clock(),
            rateLimit               : $rateLimit
        );

        try {
            $login->execute(credentials: $credentials);
            self::fail(message: 'AuthenticationFailed was not raised.');
        } catch (AuthenticationFailed $exception) {
            $this->assertSame(expected: 'Invalid credentials.', actual: $exception->getMessage());
            $this->assertSame(expected: 1, actual: $rateLimitStorage->get(identifier: 'user@example.com'));
        }
    }

    /**
     * @throws RateLimitException
     */
    public function testLoginFailedWithInactiveUser() : void
    {
        $credentials    = new Credentials(identifier: 'user@example.com', password: 'password');
        $passwordHasher = $this->passwordHasher();
        $user           = $this->userWithPassword(
            passwordHasher: $passwordHasher,
            userId        : new UserId(value: 1),
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
            startMfaChallenge       : $this->startMfaChallenge(mfaStore: new InMemoryMfaStore()),
            clock                   : new Clock(),
            rateLimit               : $rateLimit
        );

        try {
            $login->execute(credentials: $credentials);
            self::fail(message: 'AuthenticationFailed was not raised.');
        } catch (AuthenticationFailed $exception) {
            $this->assertSame(expected: 'Invalid credentials.', actual: $exception->getMessage());
            $this->assertSame(expected: 1, actual: $rateLimitStorage->get(identifier: 'user@example.com'));
        }
    }

    /**
     * @throws AuthenticationFailed
     */
    public function testLoginFailedDueToRateLimiting() : void
    {
        $credentials = new Credentials(identifier: 'user@example.com', password: 'password');

        $userSource = Mockery::mock(UserSourceInterface::class);
        $identity   = Mockery::mock(IdentityInterface::class);
        $identity->shouldNotReceive('issue');
        $identity->shouldReceive('sessionIdentity')->andReturn(null);

        $rateLimitStorage = new InMemoryLoginRateLimitStorage();
        $rateLimitStorage->increment(identifier: 'user@example.com');
        $rateLimit = new LoginRateLimit(
            storage     : $rateLimitStorage,
            clock       : new FrozenClock(now: new DateTimeImmutable()),
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
            startMfaChallenge       : $this->startMfaChallenge(mfaStore: new InMemoryMfaStore()),
            clock                   : new Clock(),
            rateLimit               : $rateLimit
        );

        $this->expectException(exception: RateLimitException::class);
        $this->expectExceptionMessage(message: 'Too many login attempts.');

        $login->execute(credentials: $credentials);
    }

    /**
     * @throws RateLimitException
     * @throws AuthenticationFailed
     */
    public function testLoginReturnsMfaChallengeWhenMfaIsEnabled() : void
    {
        $credentials    = new Credentials(identifier: 'user@example.com', password: 'password');
        $userId         = new UserId(value: 1);
        $passwordHasher = $this->passwordHasher();
        $user           = $this->userWithPassword(
            passwordHasher: $passwordHasher,
            userId        : $userId
        );

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('findByCredentials')->andReturn($user);

        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldNotReceive('issue');
        $identity->shouldReceive('sessionIdentity')->andReturn(null);

        $mfaStore = new InMemoryMfaStore();
        $mfaStore->saveMethod(record: new MfaMethodRecord(
                                          userId   : $userId,
                                          method   : MfaMethod::TOTP,
                                          secret   : 'SECRET',
                                          enabledAt: new DateTimeImmutable(datetime: '-1 minute')
                                      ));
        $startMfaChallenge = $this->startMfaChallenge(mfaStore: $mfaStore);

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
            startMfaChallenge       : $startMfaChallenge,
            clock                   : new Clock()
        );

        $result = $login->execute(credentials: $credentials);

        $this->assertTrue(condition: $result->requiresMfa());
        $this->assertNotNull(actual: $result->mfaChallengeId());
        $this->assertSame(expected: MfaChallengePurpose::LOGIN, actual: $result->mfaChallenge()?->purpose);
    }

    /**
     * @throws RateLimitException
     * @throws AuthenticationFailed
     */
    public function testLoginRehashesStoredPasswordWhenHasherPolicyChanged() : void
    {
        $credentials   = new Credentials(identifier: 'user@example.com', password: 'password');
        $userId        = new UserId(value: 1);
        $legacyHasher  = new PasswordHasher(algo: PASSWORD_BCRYPT, options: ['cost' => 4]);
        $user          = $this->userWithPassword(
            passwordHasher: $legacyHasher,
            userId        : $userId
        );
        $currentHasher = new PasswordHasher(algo: PASSWORD_BCRYPT, options: ['cost' => 12]);

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('findByCredentials')->once()->with($credentials)->andReturn($user);
        $userSource->shouldReceive('updatePassword')->once()->with(
            $userId,
            Mockery::on(closure: static fn (#[SensitiveParameter] string $hash) : bool => password_verify('password', $hash))
        );

        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('issue')->once()->with($user)->andReturn(new IssuedAuthentication(
                                                                              mode       : AuthenticationMode::TOKEN,
                                                                              accessToken: new IssuedToken(
                                                                                               token    : 'token-123',
                                                                                               tokenId  : 'access-123',
                                                                                               expiresAt: new DateTimeImmutable(datetime: '+1 hour')
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
            startMfaChallenge       : $this->startMfaChallenge(mfaStore: new InMemoryMfaStore()),
            clock                   : new Clock()
        );

        $result = $login->execute(credentials: $credentials);

        $this->assertTrue(condition: $result->isAuthenticated());
    }

    #[Override]
    protected function tearDown() : void
    {
        Mockery::close();
    }
}
