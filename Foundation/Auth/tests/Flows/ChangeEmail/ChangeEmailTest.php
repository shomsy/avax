<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\ChangeEmail;

use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Auth\System\Capabilities\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capabilities\Session\InMemorySessionRegistry;
use Avax\Auth\System\Capabilities\Session\SessionRecord;
use Avax\Auth\System\Capabilities\User\User;
use Avax\Auth\System\Capabilities\User\UserEmail;
use Avax\Auth\System\Capabilities\User\UserId;
use Avax\Auth\System\Capabilities\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flows\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flows\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flows\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flows\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flows\ChangeEmail\BeginEmailChange;
use Avax\Auth\System\Flows\ChangeEmail\BeginEmailChangeData;
use Avax\Auth\System\Flows\ChangeEmail\ConfirmEmailChange;
use Avax\Auth\System\Flows\ChangeEmail\ConfirmEmailChangeData;
use Avax\Auth\System\Flows\ChangeEmail\EmailChangeFailed;
use Avax\Auth\System\Flows\ChangeEmail\InMemoryEmailChangeStore;
use Avax\Auth\System\Flows\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flows\Mfa\Challenge\InMemoryMfaChallengeStore;
use Avax\Auth\System\Flows\Mfa\Challenge\MfaChallengeRecord;
use Avax\Auth\System\Flows\Mfa\MfaChallengePurpose;
use Avax\Auth\System\Flows\Mfa\StepUp\RequireFreshMfa;
use Avax\Auth\System\Flows\Token\InMemoryRefreshTokenStore;
use Avax\Auth\System\Flows\Verify\InMemoryEmailVerificationStateStore;
use Avax\Auth\Tests\Support\FrozenClock;
use DateInterval;
use DateInvalidOperationException;
use DateMalformedStringException;
use DateTimeImmutable;
use Mockery;
use PHPUnit\Framework\TestCase;
use Random\RandomException;
use SensitiveParameter;

final class ChangeEmailTest extends TestCase
{
    /**
     * @throws DateInvalidOperationException
     * @throws DateMalformedStringException
     * @throws Unauthenticated
     */
    public function testBeginEmailChangeSuccess() : void
    {
        $clock                 = new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-12T12:00:00+00:00'));
        $passwordHasher        = $this->passwordHasher();
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(context: AuthenticationContext::authenticated(
            user         : new AuthenticatedUser(
                               id        : 1,
                               email     : 'stale@example.com',
                               username  : 'user',
                               mfaEnabled: true
                           ),
            mode         : AuthenticationMode::SESSION,
            sessionId    : 'session-1',
            mfaVerifiedAt: $clock->now()->sub(interval: new DateInterval(duration: 'PT2M'))
        ));

        $userSource = new InMemoryUserSource();
        $userSource->create(user: $this->userWithPassword(passwordHasher: $passwordHasher, userId: 1, password: 'old-password', email: 'user@example.com'));

        $auditLog = new InMemoryAuditLog();
        $flow     = new BeginEmailChange(
            currentAuthentication: $currentAuthentication,
            userSource           : $userSource,
            passwordHasher       : $passwordHasher,
            emailChangeStore     : new InMemoryEmailChangeStore(),
            requireFreshMfa      : new RequireFreshMfa(currentAuthentication: $currentAuthentication, clock: $clock),
            auditLog             : $auditLog,
            clock                : $clock
        );

        $challenge = $flow->execute(data: new BeginEmailChangeData(
                                              newEmail       : 'new@example.com',
                                              currentPassword: 'old-password'
                                          ));

        $this->assertTrue(condition: $challenge->dispatched);
        $this->assertNotNull(actual: $challenge->token);
        $this->assertSame(expected: 'auth.email_change.requested', actual: $auditLog->events()[0]->name);
    }

    private function passwordHasher() : PasswordHasher
    {
        return new PasswordHasher(algo: PASSWORD_BCRYPT, options: ['cost' => 4]);
    }

    private function userWithPassword(#[SensitiveParameter] PasswordHasher $passwordHasher, int $userId, #[SensitiveParameter] string $password, #[SensitiveParameter] string $email) : User
    {
        return User::create(
            id          : new UserId(value: $userId),
            email       : new UserEmail(value: $email),
            username    : 'user' . $userId,
            passwordHash: $passwordHasher->hash(password: $password)
        );
    }

    /**
     * @throws DateMalformedStringException
     * @throws Unauthenticated
     * @throws DateInvalidOperationException
     */
    public function testBeginEmailChangeFailureInvalidPasswordTakesPrecedenceOverTakenEmail() : void
    {
        $clock                 = new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-12T12:00:00+00:00'));
        $passwordHasher        = $this->passwordHasher();
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(context: AuthenticationContext::authenticated(
            user         : new AuthenticatedUser(
                               id        : 1,
                               email     : 'stale@example.com',
                               username  : 'user',
                               mfaEnabled: true
                           ),
            mode         : AuthenticationMode::SESSION,
            sessionId    : 'session-1',
            mfaVerifiedAt: $clock->now()->sub(interval: new DateInterval(duration: 'PT2M'))
        ));

        $userSource = new InMemoryUserSource();
        $userSource->create(user: $this->userWithPassword(passwordHasher: $passwordHasher, userId: 1, password: 'old-password', email: 'user@example.com'));
        $userSource->create(user: $this->userWithPassword(passwordHasher: $passwordHasher, userId: 2, password: 'someone-password', email: 'taken@example.com'));

        $auditLog = new InMemoryAuditLog();
        $flow     = new BeginEmailChange(
            currentAuthentication: $currentAuthentication,
            userSource           : $userSource,
            passwordHasher       : $passwordHasher,
            emailChangeStore     : new InMemoryEmailChangeStore(),
            requireFreshMfa      : new RequireFreshMfa(currentAuthentication: $currentAuthentication, clock: $clock),
            auditLog             : $auditLog,
            clock                : $clock
        );

        $this->expectException(EmailChangeFailed::class);
        $this->expectExceptionMessage('Current password is invalid.');
        $this->expectExceptionCode(403);

        $flow->execute(data: new BeginEmailChangeData(
                                 newEmail       : 'taken@example.com',
                                 currentPassword: 'wrong-password'
                             ));
    }

    /**
     * @throws DateMalformedStringException
     * @throws DateInvalidOperationException
     * @throws Unauthenticated
     */
    public function testBeginEmailChangeFailureEmailTaken() : void
    {
        $clock                 = new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-12T12:00:00+00:00'));
        $passwordHasher        = $this->passwordHasher();
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(context: AuthenticationContext::authenticated(
            user         : new AuthenticatedUser(
                               id        : 1,
                               email     : 'stale@example.com',
                               username  : 'user',
                               mfaEnabled: true
                           ),
            mode         : AuthenticationMode::SESSION,
            sessionId    : 'session-1',
            mfaVerifiedAt: $clock->now()->sub(interval: new DateInterval(duration: 'PT2M'))
        ));

        $userSource = new InMemoryUserSource();
        $userSource->create(user: $this->userWithPassword(passwordHasher: $passwordHasher, userId: 1, password: 'old-password', email: 'user@example.com'));
        $userSource->create(user: $this->userWithPassword(passwordHasher: $passwordHasher, userId: 2, password: 'someone-password', email: 'taken@example.com'));

        $auditLog = new InMemoryAuditLog();
        $flow     = new BeginEmailChange(
            currentAuthentication: $currentAuthentication,
            userSource           : $userSource,
            passwordHasher       : $passwordHasher,
            emailChangeStore     : new InMemoryEmailChangeStore(),
            requireFreshMfa      : new RequireFreshMfa(currentAuthentication: $currentAuthentication, clock: $clock),
            auditLog             : $auditLog,
            clock                : $clock
        );

        $this->expectException(EmailChangeFailed::class);
        $this->expectExceptionMessage('Email address is already in use.');
        $this->expectExceptionCode(409);

        $flow->execute(data: new BeginEmailChangeData(
                                 newEmail       : 'taken@example.com',
                                 currentPassword: 'old-password'
                             ));
    }

    /**
     * @throws DateInvalidOperationException
     * @throws RandomException
     */
    public function testConfirmEmailChangeSuccess() : void
    {
        $clock                 = new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-12T12:00:00+00:00'));
        $passwordHasher        = $this->passwordHasher();
        $currentAuthentication = new CurrentAuthentication();
        $context               = AuthenticationContext::authenticated(
            user         : new AuthenticatedUser(
                               id        : 1,
                               email     : 'user@example.com',
                               username  : 'user',
                               mfaEnabled: true
                           ),
            mode         : AuthenticationMode::SESSION,
            sessionId    : 'session-1',
            mfaVerifiedAt: $clock->now()->sub(interval: new DateInterval(duration: 'PT2M'))
        );
        $currentAuthentication->store(context: $context);

        $userSource = new InMemoryUserSource();
        $userSource->create(user: $this->userWithPassword(passwordHasher: $passwordHasher, userId: 1, password: 'old-password', email: 'user@example.com'));

        $emailChangeStore = new InMemoryEmailChangeStore();
        $challenge        = $emailChangeStore->issue(
            userId   : new UserId(value: 1),
            newEmail : 'new@example.com',
            expiresAt: $clock->now()->add(interval: new DateInterval(duration: 'PT30M'))
        );

        $sessionRegistry = new InMemorySessionRegistry();
        $sessionRegistry->track(record: new SessionRecord(
                                            sessionId        : 'session-1',
                                            userId           : new UserId(value: 1),
                                            createdAt        : $clock->now()->sub(interval: new DateInterval(duration: 'PT5M')),
                                            lastSeenAt       : $clock->now(),
                                            idleExpiresAt    : $clock->now()->add(interval: new DateInterval(duration: 'PT10M')),
                                            absoluteExpiresAt: $clock->now()->add(interval: new DateInterval(duration: 'PT1H'))
                                        ));

        $challengeStore = new InMemoryMfaChallengeStore();
        $challengeStore->issue(record: new MfaChallengeRecord(
                                           challengeId: 'challenge-1',
                                           userId     : new UserId(value: 1),
                                           purpose    : MfaChallengePurpose::LOGIN,
                                           createdAt  : $clock->now(),
                                           expiresAt  : $clock->now()->add(interval: new DateInterval(duration: 'PT5M'))
                                       ));

        $refreshTokenStore = new InMemoryRefreshTokenStore();
        $refreshToken      = $refreshTokenStore->issue(
            userId   : new UserId(value: 1),
            expiresAt: $clock->now()->add(interval: new DateInterval(duration: 'PT1H'))
        );

        $emailVerificationState = new InMemoryEmailVerificationStateStore();
        $auditLog               = new InMemoryAuditLog();
        $identity               = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('clear')->once()->with($context);

        $flow = new ConfirmEmailChange(
            userSource            : $userSource,
            emailChangeStore      : $emailChangeStore,
            emailVerificationState: $emailVerificationState,
            auditLog              : $auditLog,
            clock                 : $clock,
            currentAuthentication : $currentAuthentication,
            identity              : $identity,
            sessionRegistry       : $sessionRegistry,
            mfaChallengeStore     : $challengeStore,
            refreshTokenStore     : $refreshTokenStore
        );

        $this->assertTrue(condition: $flow->execute(data: new ConfirmEmailChangeData(token: $challenge->token ?? '')));
        $this->assertSame(expected: 'new@example.com', actual: $userSource->findById(id: new UserId(value: 1))?->getEmail()->value);
        $this->assertTrue(condition: $emailVerificationState->isVerified(userId: new UserId(value: 1)));
        $this->assertTrue(condition: $sessionRegistry->find(sessionId: 'session-1')?->isRevoked() ?? false);
        $this->assertSame(expected: 'email_change', actual: $sessionRegistry->find(sessionId: 'session-1')?->revokeReason);
        $this->assertNull(actual: $challengeStore->find(challengeId: 'challenge-1'));
        $this->assertTrue(condition: $refreshTokenStore->find(plainToken: $refreshToken->token)?->revoked ?? false);
        $this->assertNull(actual: $currentAuthentication->read()->user());
        $this->assertSame(expected: 'auth.email_change.completed', actual: $auditLog->events()[0]->name);
    }

    public function testConfirmEmailChangeFailureInvalidToken() : void
    {
        $clock = new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-12T12:00:00+00:00'));
        $flow  = new ConfirmEmailChange(
            userSource            : new InMemoryUserSource(),
            emailChangeStore      : new InMemoryEmailChangeStore(),
            emailVerificationState: new InMemoryEmailVerificationStateStore(),
            auditLog              : new InMemoryAuditLog(),
            clock                 : $clock,
            currentAuthentication : new CurrentAuthentication(),
            identity              : Mockery::mock(IdentityInterface::class)
        );

        $this->expectException(EmailChangeFailed::class);
        $this->expectExceptionMessage('Email change token is invalid.');
        $this->expectExceptionCode(410);

        $flow->execute(data: new ConfirmEmailChangeData(token: 'invalid-token'));
    }

    protected function tearDown() : void
    {
        Mockery::close();
    }
}
