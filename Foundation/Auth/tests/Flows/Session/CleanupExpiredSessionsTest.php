<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Session;

use Avax\Auth\System\Capability\Session\InMemorySessionRegistry;
use Avax\Auth\System\Capability\Session\SessionRecord;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Flow\Session\CleanupExpiredSessions\CleanupExpiredSessions;
use Avax\Auth\System\Foundation\Clock;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class CleanupExpiredSessionsTest extends TestCase
{
    /**
     * @throws \DateMalformedStringException
     */
    public function testCleanupRemovesExpiredAndRevokedSessions() : void
    {
        $registry = new InMemorySessionRegistry();
        $now      = new DateTimeImmutable();
        $registry->track(record: new SessionRecord(
            sessionId        : 'expired',
            userId           : new UserId(value: 1),
            createdAt        : $now->modify(modifier: '-2 hours'),
            lastSeenAt       : $now->modify(modifier: '-2 hours'),
            idleExpiresAt    : $now->modify(modifier: '-1 hour'),
            absoluteExpiresAt: $now->modify(modifier: '-30 minutes')
        ));
        $registry->track(record: new SessionRecord(
            sessionId        : 'active',
            userId           : new UserId(value: 1),
            createdAt        : $now,
            lastSeenAt       : $now,
            idleExpiresAt    : $now->modify(modifier: '+1 hour'),
            absoluteExpiresAt: $now->modify(modifier: '+2 hours')
        ));
        $registry->track(record: new SessionRecord(
            sessionId        : 'revoked',
            userId           : new UserId(value: 1),
            createdAt        : $now,
            lastSeenAt       : $now,
            idleExpiresAt    : $now->modify(modifier: '+1 hour'),
            absoluteExpiresAt: $now->modify(modifier: '+2 hours'),
            revokedAt        : $now,
            revokeReason     : 'manual'
        ));

        $removed = (new CleanupExpiredSessions(sessionRegistry: $registry, clock: new Clock()))->execute();

        $this->assertSame(expected: 2, actual: $removed);
        $this->assertNotNull(actual: $registry->find(sessionId: 'active'));
        $this->assertNull(actual: $registry->find(sessionId: 'expired'));
        $this->assertNull(actual: $registry->find(sessionId: 'revoked'));
    }
}
