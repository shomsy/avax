<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Session;

use Avax\Auth\System\Capability\Session\InMemorySessionRegistry;
use Avax\Auth\System\Capability\Session\SessionRecord;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Flow\Session\CleanupExpiredSessions\CleanupExpiredSessions;
use Avax\Auth\System\Foundation\Clock;
use PHPUnit\Framework\TestCase;

final class CleanupExpiredSessionsTest extends TestCase
{
    public function testCleanupRemovesExpiredAndRevokedSessions() : void
    {
        $registry = new InMemorySessionRegistry();
        $now      = new \DateTimeImmutable();
        $registry->track(new SessionRecord(
            sessionId        : 'expired',
            userId           : new UserId(1),
            createdAt        : $now->modify('-2 hours'),
            lastSeenAt       : $now->modify('-2 hours'),
            idleExpiresAt    : $now->modify('-1 hour'),
            absoluteExpiresAt: $now->modify('-30 minutes')
        ));
        $registry->track(new SessionRecord(
            sessionId        : 'active',
            userId           : new UserId(1),
            createdAt        : $now,
            lastSeenAt       : $now,
            idleExpiresAt    : $now->modify('+1 hour'),
            absoluteExpiresAt: $now->modify('+2 hours')
        ));
        $registry->track(new SessionRecord(
            sessionId        : 'revoked',
            userId           : new UserId(1),
            createdAt        : $now,
            lastSeenAt       : $now,
            idleExpiresAt    : $now->modify('+1 hour'),
            absoluteExpiresAt: $now->modify('+2 hours'),
            revokedAt        : $now,
            revokeReason     : 'manual'
        ));

        $removed = (new CleanupExpiredSessions($registry, new Clock()))->execute();

        $this->assertSame(2, $removed);
        $this->assertNotNull($registry->find('active'));
        $this->assertNull($registry->find('expired'));
        $this->assertNull($registry->find('revoked'));
    }
}
