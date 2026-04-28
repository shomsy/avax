<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Tests\Flows\Mfa\Recover;

use Avax\Auth\Tests\Support\FrozenClock;
use Avax\Components\Identity\Auth\System\Capabilities\Access\Authentication\Throttle\AttemptThrottle;
use Avax\Components\Identity\Auth\System\Capabilities\Access\Authentication\Throttle\InMemoryAttemptThrottleStore;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\InMemoryAuditLog;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Enums\MfaMethod;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Models\MfaRecoveryFailed;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Records\MfaMethodRecord;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\BeginMfaRecoveryData;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\ConfirmMfaRecovery;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\ConfirmMfaRecoveryData;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Recover\StartMfaRecovery;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Stores\InMemoryMfaStore;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Verify\InMemoryMfaChallengeStore;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\InMemoryUserSource;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Tests\TestCase;
use DateMalformedStringException;
use DateTimeImmutable;
use Mockery;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

/**
 * Unit tests for MFA recovery.
 */
final class MfaRecoveryTest extends TestCase
{
    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
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

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
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

    /**
     * @throws DateMalformedStringException
     * @throws RandomException
     */
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

        $first  = $start->execute(data: new BeginMfaRecoveryData(
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
