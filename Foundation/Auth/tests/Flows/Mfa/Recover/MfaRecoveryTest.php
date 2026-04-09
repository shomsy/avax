<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Mfa\Recover;

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
        $clock      = new FrozenClock(new DateTimeImmutable('2026-04-09T12:00:00+00:00'));
        $userSource = new InMemoryUserSource();
        $user       = User::create(
            id          : new UserId(1),
            email       : new UserEmail('user@example.com'),
            username    : 'user',
            passwordHash: 'hash'
        );
        $userSource->create($user);

        $mfaStore = new InMemoryMfaStore();
        $mfaStore->saveMethod(new MfaMethodRecord(
                                  userId   : new UserId(1),
                                  method   : MfaMethod::TOTP,
                                  secret   : 'SECRETSECRETSECRETSECRETSECRETSE',
                                  enabledAt: $clock->now()
                              ));
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(AuthenticationContext::authenticated(
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
        $refreshTokens->shouldReceive('revokeUser')->once()->with(Mockery::type(UserId::class));
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

        $challenge = $start->execute(new BeginMfaRecoveryData('user@example.com'));
        $this->assertTrue($challenge->dispatched);
        $this->assertNotNull($challenge->token);

        $confirm->execute(new ConfirmMfaRecoveryData($challenge->token ?? ''));

        $this->assertFalse($mfaStore->isEnabled(new UserId(1)));
        $this->assertFalse($currentAuthentication->read()->isAuthenticated());
    }

    public function testRecoveryUsesAntiEnumerationForMissingUser() : void
    {
        $start = new StartMfaRecovery(
            userSource: new InMemoryUserSource(),
            mfaStore  : new InMemoryMfaStore(),
            auditLog  : new InMemoryAuditLog(),
            clock     : new FrozenClock(new DateTimeImmutable('2026-04-09T12:00:00+00:00'))
        );

        $challenge = $start->execute(new BeginMfaRecoveryData('missing@example.com'));

        $this->assertTrue($challenge->dispatched);
        $this->assertNull($challenge->token);
    }

    public function testRecoveryRejectsUnknownToken() : void
    {
        $confirm = new ConfirmMfaRecovery(
            mfaStore         : new InMemoryMfaStore(),
            mfaChallengeStore: new InMemoryMfaChallengeStore(),
            auditLog         : new InMemoryAuditLog(),
            clock            : new FrozenClock(new DateTimeImmutable('2026-04-09T12:00:00+00:00'))
        );

        $this->expectException(MfaRecoveryFailed::class);
        $this->expectExceptionMessage('MFA recovery token is invalid.');
        $confirm->execute(new ConfirmMfaRecoveryData('missing-token'));
    }

    protected function tearDown() : void
    {
        Mockery::close();
    }
}
