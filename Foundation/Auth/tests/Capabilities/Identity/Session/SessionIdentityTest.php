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
    /**
     * @throws \DateMalformedStringException
     */
    public function testIssueStoresUserAndReturnsRegeneratedSessionId() : void
    {
        $store    = new ArraySessionStore();
        $identity = new SessionIdentity(
            store   : $store,
            clock   : new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-12T10:00:00+00:00')),
            lifetime: new SessionLifetime(idleTimeoutSeconds: 900, absoluteTimeoutSeconds: 43200)
        );

        $sessionId = $identity->issue(userId: 42);

        $this->assertSame(expected: 'session-1', actual: $sessionId);
        $this->assertSame(expected: 42, actual: $identity->resolveUserId());
        $this->assertSame(expected: 'session-1', actual: $identity->currentSessionId());
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function testResolveUserIdExpiresIdleSession() : void
    {
        $clock    = new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-12T10:00:00+00:00'));
        $auditLog = new InMemoryAuditLog();
        $identity = new SessionIdentity(
            store   : new ArraySessionStore(),
            clock   : $clock,
            auditLog: $auditLog,
            lifetime: new SessionLifetime(idleTimeoutSeconds: 300, absoluteTimeoutSeconds: 3600)
        );

        $identity->issue(userId: 42);
        $clock->advance(interval: new DateInterval(duration: 'PT6M'));

        $this->assertNull(actual: $identity->resolveUserId());
        $this->assertNull(actual: $identity->currentSessionId());
        $this->assertSame(expected: 'auth.session.expired', actual: $auditLog->events()[0]->name);
        $this->assertSame(expected: 'idle_timeout', actual: $auditLog->events()[0]->context['reason']);
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function testResolveUserIdExpiresAbsoluteSession() : void
    {
        $clock    = new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-12T10:00:00+00:00'));
        $auditLog = new InMemoryAuditLog();
        $identity = new SessionIdentity(
            store   : new ArraySessionStore(),
            clock   : $clock,
            auditLog: $auditLog,
            lifetime: new SessionLifetime(idleTimeoutSeconds: 900, absoluteTimeoutSeconds: 1800)
        );

        $identity->issue(userId: 42);
        $clock->advance(interval: new DateInterval(duration: 'PT31M'));

        $this->assertNull(actual: $identity->resolveUserId());
        $this->assertSame(expected: 'absolute_timeout', actual: $auditLog->events()[0]->context['reason']);
    }

    /**
     * @throws \DateMalformedStringException
     */
    public function testIssueRegeneratesExistingSessionIdToPreventFixation() : void
    {
        $store = new ArraySessionStore();
        $store->start();
        $preLoginSessionId = $store->id();

        $identity = new SessionIdentity(
            store   : $store,
            clock   : new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-12T10:00:00+00:00')),
            lifetime: new SessionLifetime(idleTimeoutSeconds: 900, absoluteTimeoutSeconds: 43200)
        );

        $issuedSessionId = $identity->issue(userId: 42);

        $this->assertNotSame(expected: $preLoginSessionId, actual: $issuedSessionId);
        $this->assertSame(expected: 'session-2', actual: $issuedSessionId);
        $this->assertSame(expected: 42, actual: $identity->resolveUserId());
    }
}
