<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Tests\Flows\Recover;

use Avax\Components\Identity\Auth\System\Capabilities\Access\Authentication\Throttle\AttemptThrottle;
use Avax\Components\Identity\Auth\System\Capabilities\Access\Authentication\Throttle\InMemoryAttemptThrottleStore;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\InMemoryAuditLog;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource\InMemoryUserSource;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\BeginPasswordReset;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\BeginPasswordResetData;
use Avax\Components\Identity\Auth\System\Flows\RecoverAccess\PasswordReset\InMemoryPasswordResetStore;
use Avax\Components\Identity\Auth\Tests\Support\FrozenClock;
use Avax\Components\Tests\TestCase;
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
