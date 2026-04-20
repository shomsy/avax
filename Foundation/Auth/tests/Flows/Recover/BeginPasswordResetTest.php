<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\Recover;

use Avax\Auth\System\Capabilities\Throttle\AttemptThrottle;
use Avax\Auth\System\Capabilities\Throttle\InMemoryAttemptThrottleStore;
use Avax\Auth\System\Capabilities\User\User;
use Avax\Auth\System\Capabilities\User\UserEmail;
use Avax\Auth\System\Capabilities\User\UserId;
use Avax\Auth\System\Capabilities\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flows\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flows\Recover\BeginPasswordReset;
use Avax\Auth\System\Flows\Recover\BeginPasswordResetData;
use Avax\Auth\System\Flows\Recover\InMemoryPasswordResetStore;
use Avax\Auth\Tests\Support\FrozenClock;
use DateMalformedStringException;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for password reset start flow hardening.
 */
final class BeginPasswordResetTest extends TestCase
{
    /**
     * @throws DateMalformedStringException
     */
    public function testPasswordResetRequestsAreThrottledAfterConfiguredLimit() : void
    {
        $clock      = new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-12T10:00:00+00:00'));
        $auditLog   = new InMemoryAuditLog();
        $userSource = new InMemoryUserSource();
        $userSource->create(user: User::create(
            id          : new UserId(value: 1),
            email       : new UserEmail(value: 'user@example.com'),
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

        $first  = $flow->execute(data: new BeginPasswordResetData(
                                           email    : 'user@example.com',
                                           ipAddress: '127.0.0.1',
                                           userAgent: 'PHPUnit'
                                       ));
        $second = $flow->execute(data: new BeginPasswordResetData(
                                           email    : 'user@example.com',
                                           ipAddress: '127.0.0.1',
                                           userAgent: 'PHPUnit'
                                       ));

        $this->assertNotNull(actual: $first->token);
        $this->assertNull(actual: $second->token);
        $this->assertSame(expected: 'auth.password_reset.throttled', actual: $auditLog->events()[1]->name);
    }
}
