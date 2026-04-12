<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Recover;

use Avax\Auth\System\Capability\Throttle\AttemptThrottle;
use Avax\Auth\System\Capability\Throttle\InMemoryAttemptThrottleStore;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserEmail;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flow\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flow\Recover\BeginPasswordReset;
use Avax\Auth\System\Flow\Recover\BeginPasswordResetData;
use Avax\Auth\System\Flow\Recover\InMemoryPasswordResetStore;
use Avax\Auth\Tests\Support\FrozenClock;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for password reset start flow hardening.
 */
final class BeginPasswordResetTest extends TestCase
{
    public function testPasswordResetRequestsAreThrottledAfterConfiguredLimit() : void
    {
        $clock      = new FrozenClock(new DateTimeImmutable('2026-04-12T10:00:00+00:00'));
        $auditLog   = new InMemoryAuditLog();
        $userSource = new InMemoryUserSource();
        $userSource->create(User::create(
            id          : new UserId(1),
            email       : new UserEmail('user@example.com'),
            username    : 'user',
            passwordHash: 'hash'
        ));
        $flow = new BeginPasswordReset(
            userSource        : $userSource,
            passwordResetStore: new InMemoryPasswordResetStore(),
            auditLog          : $auditLog,
            clock             : $clock,
            attemptThrottle   : new AttemptThrottle(
                store       : new InMemoryAttemptThrottleStore(),
                clock       : $clock,
                maxAttempts : 1,
                decaySeconds: 900
            )
        );

        $first = $flow->execute(new BeginPasswordResetData(
            email    : 'user@example.com',
            ipAddress: '127.0.0.1',
            userAgent: 'PHPUnit'
        ));
        $second = $flow->execute(new BeginPasswordResetData(
            email    : 'user@example.com',
            ipAddress: '127.0.0.1',
            userAgent: 'PHPUnit'
        ));

        $this->assertNotNull($first->token);
        $this->assertNull($second->token);
        $this->assertSame('auth.password_reset.throttled', $auditLog->events()[1]->name);
    }
}
