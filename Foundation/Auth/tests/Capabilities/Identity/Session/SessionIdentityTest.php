<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\Identity\Session;

use Avax\Auth\System\Capability\Identity\Session\SessionIdentity;
use Avax\Auth\System\Capability\Identity\Session\SessionLifetime;
use Avax\Auth\System\Flow\Diagnostics\InMemoryAuditLog;
use Avax\Auth\Tests\Support\ArraySessionStore;
use Avax\Auth\Tests\Support\FrozenClock;
use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for session lifetime enforcement.
 */
final class SessionIdentityTest extends TestCase
{
    public function testIssueStoresUserAndReturnsRegeneratedSessionId() : void
    {
        $store    = new ArraySessionStore();
        $identity = new SessionIdentity(
            store   : $store,
            clock   : new FrozenClock(new DateTimeImmutable('2026-04-12T10:00:00+00:00')),
            lifetime: new SessionLifetime(idleTimeoutSeconds: 900, absoluteTimeoutSeconds: 43200)
        );

        $sessionId = $identity->issue(userId: 42);

        $this->assertSame('session-1', $sessionId);
        $this->assertSame(42, $identity->resolveUserId());
        $this->assertSame('session-1', $identity->currentSessionId());
    }

    public function testResolveUserIdExpiresIdleSession() : void
    {
        $clock    = new FrozenClock(new DateTimeImmutable('2026-04-12T10:00:00+00:00'));
        $auditLog = new InMemoryAuditLog();
        $identity = new SessionIdentity(
            store   : new ArraySessionStore(),
            clock   : $clock,
            auditLog: $auditLog,
            lifetime: new SessionLifetime(idleTimeoutSeconds: 300, absoluteTimeoutSeconds: 3600)
        );

        $identity->issue(userId: 42);
        $clock->advance(new DateInterval('PT6M'));

        $this->assertNull($identity->resolveUserId());
        $this->assertNull($identity->currentSessionId());
        $this->assertSame('auth.session.expired', $auditLog->events()[0]->name);
        $this->assertSame('idle_timeout', $auditLog->events()[0]->context['reason']);
    }

    public function testResolveUserIdExpiresAbsoluteSession() : void
    {
        $clock    = new FrozenClock(new DateTimeImmutable('2026-04-12T10:00:00+00:00'));
        $auditLog = new InMemoryAuditLog();
        $identity = new SessionIdentity(
            store   : new ArraySessionStore(),
            clock   : $clock,
            auditLog: $auditLog,
            lifetime: new SessionLifetime(idleTimeoutSeconds: 900, absoluteTimeoutSeconds: 1800)
        );

        $identity->issue(userId: 42);
        $clock->advance(new DateInterval('PT31M'));

        $this->assertNull($identity->resolveUserId());
        $this->assertSame('absolute_timeout', $auditLog->events()[0]->context['reason']);
    }
}
