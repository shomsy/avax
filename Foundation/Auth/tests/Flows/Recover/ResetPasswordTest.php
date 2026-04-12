<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Recover;

use Avax\Auth\System\Capability\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capability\Session\InMemorySessionRegistry;
use Avax\Auth\System\Capability\Session\SessionRecord;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserEmail;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flow\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flow\Mfa\Challenge\InMemoryMfaChallengeStore;
use Avax\Auth\System\Flow\Mfa\Challenge\MfaChallengeRecord;
use Avax\Auth\System\Flow\Mfa\MfaChallengePurpose;
use Avax\Auth\System\Flow\Recover\InMemoryPasswordResetStore;
use Avax\Auth\System\Flow\Recover\ResetPassword;
use Avax\Auth\System\Flow\Recover\ResetPasswordData;
use Avax\Auth\System\Flow\Token\RefreshTokenStoreInterface;
use Avax\Auth\Tests\Support\FrozenClock;
use DateInterval;
use DateTimeImmutable;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for password reset completion hardening.
 */
final class ResetPasswordTest extends TestCase
{
    public function testResetPasswordRevokesSessionsAndChallenges() : void
    {
        $clock             = new FrozenClock(new DateTimeImmutable('2026-04-12T12:00:00+00:00'));
        $passwordHasher    = new PasswordHasher(algo: PASSWORD_BCRYPT, options: ['cost' => 4]);
        $userSource        = new InMemoryUserSource();
        $passwordResetStore = new InMemoryPasswordResetStore();
        $user              = User::create(
            id          : new UserId(1),
            email       : new UserEmail('user@example.com'),
            username    : 'user',
            passwordHash: $passwordHasher->hash('old-password')
        );
        $userSource->create($user);
        $challenge = $passwordResetStore->issue(new UserId(1), $clock->now()->add(new DateInterval('PT1H')));

        $sessionRegistry = new InMemorySessionRegistry();
        $sessionRegistry->track(new SessionRecord(
            sessionId        : 'session-1',
            userId           : new UserId(1),
            createdAt        : $clock->now()->sub(new DateInterval('PT30M')),
            lastSeenAt       : $clock->now(),
            idleExpiresAt    : $clock->now()->add(new DateInterval('PT15M')),
            absoluteExpiresAt: $clock->now()->add(new DateInterval('PT12H'))
        ));
        $challengeStore = new InMemoryMfaChallengeStore();
        $challengeStore->issue(new MfaChallengeRecord(
            challengeId: 'challenge-1',
            userId     : new UserId(1),
            purpose    : MfaChallengePurpose::LOGIN,
            createdAt  : $clock->now(),
            expiresAt  : $clock->now()->add(new DateInterval('PT5M'))
        ));

        $refreshTokens = Mockery::mock(RefreshTokenStoreInterface::class);
        $refreshTokens->shouldReceive('revokeUser')->once()->with(Mockery::type(UserId::class));

        $flow = new ResetPassword(
            userSource        : $userSource,
            passwordHasher    : $passwordHasher,
            passwordResetStore: $passwordResetStore,
            auditLog          : new InMemoryAuditLog(),
            clock             : $clock,
            sessionRegistry   : $sessionRegistry,
            mfaChallengeStore : $challengeStore,
            refreshTokenStore : $refreshTokens
        );

        $result = $flow->execute(new ResetPasswordData(
            token      : $challenge->token ?? '',
            newPassword: 'new-password'
        ));

        $this->assertTrue($result);
        $this->assertSame('password_reset', $sessionRegistry->find('session-1')?->revokeReason);
        $this->assertNull($challengeStore->find('challenge-1'));
        $this->assertTrue($passwordHasher->verify('new-password', $userSource->findById(new UserId(1))?->getPasswordHash() ?? ''));
    }

    protected function tearDown() : void
    {
        Mockery::close();
    }
}
