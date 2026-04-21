<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\Recover;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\InMemoryAuditLog;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Verify\InMemoryMfaChallengeStore;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Verify\MfaChallengeRecord;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Enums\MfaChallengePurpose;
use Avax\Auth\System\Capabilities\Identity\PasswordHashing\PasswordHasher;
use Avax\Auth\System\Capabilities\Identity\Sessions\Registry\InMemorySessionRegistry;
use Avax\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRecord;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use Avax\Auth\System\Capabilities\Identity\User\User;
use Avax\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Auth\System\Capabilities\Identity\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flows\RecoverAccess\PasswordReset\InMemoryPasswordResetStore;
use Avax\Auth\System\Flows\RecoverAccess\PasswordReset\ResetPassword;
use Avax\Auth\System\Flows\RecoverAccess\PasswordReset\ResetPasswordData;
use Avax\Auth\Tests\Support\FrozenClock;
use DateInterval;
use DateInvalidOperationException;
use DateTimeImmutable;
use Mockery;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

/**
 * Unit tests for password reset completion hardening.
 */
final class ResetPasswordTest extends TestCase
{
    /**
     * @throws DateInvalidOperationException
     * @throws RandomException
     */
    public function testResetPasswordRevokesSessionsAndChallenges() : void
    {
        $clock              = new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-12T12:00:00+00:00'));
        $passwordHasher     = new PasswordHasher(algo: PASSWORD_BCRYPT, options: ['cost' => 4]);
        $userSource         = new InMemoryUserSource();
        $passwordResetStore = new InMemoryPasswordResetStore();
        $user               = User::create(
            id          : new UserId(value: 1),
            email       : new UserEmail(value: 'user@example.com'),
            username    : 'user',
            passwordHash: $passwordHasher->hash(password: 'old-password')
        );
        $userSource->create(user: $user);
        $challenge = $passwordResetStore->issue(userId: new UserId(value: 1), expiresAt: $clock->now()->add(interval: new DateInterval(duration: 'PT1H')));

        $sessionRegistry = new InMemorySessionRegistry();
        $sessionRegistry->track(record: new SessionRecord(
                                            sessionId        : 'session-1',
                                            userId           : new UserId(value: 1),
                                            createdAt        : $clock->now()->sub(interval: new DateInterval(duration: 'PT30M')),
                                            lastSeenAt       : $clock->now(),
                                            idleExpiresAt    : $clock->now()->add(interval: new DateInterval(duration: 'PT15M')),
                                            absoluteExpiresAt: $clock->now()->add(interval: new DateInterval(duration: 'PT12H'))
                                        ));
        $challengeStore = new InMemoryMfaChallengeStore();
        $challengeStore->issue(record: new MfaChallengeRecord(
                                           challengeId: 'challenge-1',
                                           userId     : new UserId(value: 1),
                                           purpose    : MfaChallengePurpose::LOGIN,
                                           createdAt  : $clock->now(),
                                           expiresAt  : $clock->now()->add(interval: new DateInterval(duration: 'PT5M'))
                                       ));

        $refreshTokens = Mockery::mock(RefreshTokenStoreInterface::class);
        $refreshTokens->shouldReceive('revokeUser')->once()->with(Mockery::type(expected: UserId::class));

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

        $result = $flow->execute(data: new ResetPasswordData(
                                           token      : $challenge->token ?? '',
                                           newPassword: 'new-password'
                                       ));

        $this->assertTrue(condition: $result);
        $this->assertSame(expected: 'password_reset', actual: $sessionRegistry->find(sessionId: 'session-1')?->revokeReason);
        $this->assertNull(actual: $challengeStore->find(challengeId: 'challenge-1'));
        $this->assertTrue(condition: $passwordHasher->verify(password: 'new-password', hash: $userSource->findById(id: new UserId(value: 1))?->getPasswordHash() ?? ''));
    }

    protected function tearDown() : void
    {
        Mockery::close();
    }
}
