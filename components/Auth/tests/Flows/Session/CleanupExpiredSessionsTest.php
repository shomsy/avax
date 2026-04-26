<?php

declare(strict_types=1);

namespace components\Auth\Tests\Flows\Session;

use components\Auth\System\Capabilities\Identity\Sessions\Registry\InMemorySessionRegistry;
use components\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRecord;
use components\Auth\System\Capabilities\Identity\Sessions\Runtime\CleanupExpiredSessions\CleanupExpiredSessions;
use components\Auth\System\Capabilities\Identity\User\UserId;
use components\Auth\System\Foundation\Clock;
use components\Tests\TestCase;
use DateMalformedStringException;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class CleanupExpiredSessionsTest extends TestCase
{
    /**
     * @throws DateMalformedStringException
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

        $removed = new CleanupExpiredSessions(sessionRegistry: $registry, clock: new Clock())->execute();

        $this->assertSame(expected: 2, actual: $removed);
        $this->assertNotNull(actual: $registry->find(sessionId: 'active'));
        $this->assertNull(actual: $registry->find(sessionId: 'expired'));
        $this->assertNull(actual: $registry->find(sessionId: 'revoked'));
    }
}
