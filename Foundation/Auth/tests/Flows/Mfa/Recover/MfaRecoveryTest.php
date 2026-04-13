<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Mfa\Recover;

use Avax\Auth\System\Capability\Throttle\AttemptThrottle;
use Avax\Auth\System\Capability\Throttle\InMemoryAttemptThrottleStore;
use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserEmail;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flow\Mfa\Challenge\InMemoryMfaChallengeStore;
use Avax\Auth\System\Flow\Mfa\InMemoryMfaStore;
use Avax\Auth\System\Flow\Mfa\MfaMethod;
use Avax\Auth\System\Flow\Mfa\MfaMethodRecord;
use Avax\Auth\System\Flow\Mfa\MfaRecoveryFailed;
use Avax\Auth\System\Flow\Mfa\Recover\BeginMfaRecoveryData;
use Avax\Auth\System\Flow\Mfa\Recover\ConfirmMfaRecovery;
use Avax\Auth\System\Flow\Mfa\Recover\ConfirmMfaRecoveryData;
use Avax\Auth\System\Flow\Mfa\Recover\StartMfaRecovery;
use Avax\Auth\System\Flow\Token\RefreshTokenStoreInterface;
use Avax\Auth\Tests\Support\FrozenClock;
use DateTimeImmutable;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for MFA recovery.
 */
final class MfaRecoveryTest extends TestCase
{
    public function testRecoveryResetsMfaAndClearsCurrentSession() : void
    {
        $clock      = new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-09T12:00:00+00:00'));
        $userSource = new InMemoryUserSource();
        $user       = User::create(
            id          : new UserId(value: 1),
            email       : new UserEmail(value: 'user@example.com'),
            username    : 'user',
            passwordHash: 'hash'
        );
        $userSource->create(user: $user);

        $mfaStore = new InMemoryMfaStore();
        $mfaStore->saveMethod(record: new MfaMethodRecord(
                                  userId   : new UserId(value: 1),
                                  method   : MfaMethod::TOTP,
                                  secret   : 'SECRETSECRETSECRETSECRETSECRETSE',
                                  enabledAt: $clock->now()
                              ));
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(context: AuthenticationContext::authenticated(
            user         : new AuthenticatedUser(
                               id        : 1,
                               email     : 'user@example.com',
                               username  : 'user',
                               mfaEnabled: true
                           ),
            mode         : AuthenticationMode::TOKEN,
            mfaVerifiedAt: $clock->now()
        ));
        $refreshTokens = Mockery::mock(RefreshTokenStoreInterface::class);
        $refreshTokens->shouldReceive('revokeUser')->once()->with(Mockery::type(expected: UserId::class));
        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('clear')->once();

        $start   = new StartMfaRecovery(
            userSource: $userSource,
            mfaStore  : $mfaStore,
            auditLog  : new InMemoryAuditLog(),
            clock     : $clock
        );
        $confirm = new ConfirmMfaRecovery(
            mfaStore             : $mfaStore,
            mfaChallengeStore    : new InMemoryMfaChallengeStore(),
            auditLog             : new InMemoryAuditLog(),
            clock                : $clock,
            refreshTokenStore    : $refreshTokens,
            currentAuthentication: $currentAuthentication,
            identity             : $identity
        );

        $challenge = $start->execute(data: new BeginMfaRecoveryData(email: 'user@example.com'));
        $this->assertTrue(condition: $challenge->dispatched);
        $this->assertNotNull(actual: $challenge->token);

        $confirm->execute(data: new ConfirmMfaRecoveryData(token: $challenge->token ?? ''));

        $this->assertFalse(condition: $mfaStore->isEnabled(userId: new UserId(value: 1)));
        $this->assertFalse(condition: $currentAuthentication->read()->isAuthenticated());
    }

    public function testRecoveryUsesAntiEnumerationForMissingUser() : void
    {
        $start = new StartMfaRecovery(
            userSource: new InMemoryUserSource(),
            mfaStore  : new InMemoryMfaStore(),
            auditLog  : new InMemoryAuditLog(),
            clock     : new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-09T12:00:00+00:00'))
        );

        $challenge = $start->execute(data: new BeginMfaRecoveryData(email: 'missing@example.com'));

        $this->assertTrue(condition: $challenge->dispatched);
        $this->assertNull(actual: $challenge->token);
    }

    public function testRecoveryRejectsUnknownToken() : void
    {
        $confirm = new ConfirmMfaRecovery(
            mfaStore         : new InMemoryMfaStore(),
            mfaChallengeStore: new InMemoryMfaChallengeStore(),
            auditLog         : new InMemoryAuditLog(),
            clock            : new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-09T12:00:00+00:00'))
        );

        $this->expectException(MfaRecoveryFailed::class);
        $this->expectExceptionMessage('MFA recovery token is invalid.');
        $confirm->execute(data: new ConfirmMfaRecoveryData(token: 'missing-token'));
    }

    public function testRecoveryStartIsThrottledAfterConfiguredLimit() : void
    {
        $clock      = new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-09T12:00:00+00:00'));
        $userSource = new InMemoryUserSource();
        $userSource->create(user: User::create(
            id          : new UserId(value: 1),
            email       : new UserEmail(value: 'user@example.com'),
            username    : 'user',
            passwordHash: 'hash'
        ));
        $mfaStore = new InMemoryMfaStore();
        $mfaStore->saveMethod(record: new MfaMethodRecord(
            userId   : new UserId(value: 1),
            method   : MfaMethod::TOTP,
            secret   : 'SECRETSECRETSECRETSECRETSECRETSE',
            enabledAt: $clock->now()
        ));
        $auditLog = new InMemoryAuditLog();
        $start    = new StartMfaRecovery(
            userSource     : $userSource,
            mfaStore       : $mfaStore,
            auditLog       : $auditLog,
            clock          : $clock,
            attemptThrottle: new AttemptThrottle(
                store       : new InMemoryAttemptThrottleStore(),
                clock       : $clock,
                maxAttempts : 1,
                decaySeconds: 900
            )
        );

        $first = $start->execute(data: new BeginMfaRecoveryData(
            email    : 'user@example.com',
            ipAddress: '127.0.0.1',
            userAgent: 'PHPUnit'
        ));
        $second = $start->execute(data: new BeginMfaRecoveryData(
            email    : 'user@example.com',
            ipAddress: '127.0.0.1',
            userAgent: 'PHPUnit'
        ));

        $this->assertNotNull(actual: $first->token);
        $this->assertNull(actual: $second->token);
        $this->assertSame(expected: 'auth.mfa.recovery.throttled', actual: $auditLog->events()[1]->name);
    }

    protected function tearDown() : void
    {
        Mockery::close();
    }
}
