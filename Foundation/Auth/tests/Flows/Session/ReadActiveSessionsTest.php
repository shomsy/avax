<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Session;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\Session\InMemorySessionRegistry;
use Avax\Auth\System\Capability\Session\SessionRegistryUnavailable;
use Avax\Auth\System\Capability\Session\SessionRecord;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Session\ReadActiveSessions\ReadActiveSessions;
use Avax\Auth\Tests\Support\FrozenClock;
use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for active session listing.
 */
final class ReadActiveSessionsTest extends TestCase
{
    /**
     * @throws \DateInvalidOperationException
     * @throws Unauthenticated
     */
    public function testReadActiveSessionsReturnsCurrentUsersTrackedSessions() : void
    {
        $clock    = new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-12T12:00:00+00:00'));
        $registry = new InMemorySessionRegistry();
        $registry->track(record: new SessionRecord(
            sessionId        : 'session-1',
            userId           : new UserId(value: 1),
            createdAt        : $clock->now()->sub(interval: new DateInterval(duration: 'PT30M')),
            lastSeenAt       : $clock->now(),
            idleExpiresAt    : $clock->now()->add(interval: new DateInterval(duration: 'PT15M')),
            absoluteExpiresAt: $clock->now()->add(interval: new DateInterval(duration: 'PT12H')),
            ipCreated        : '127.0.0.1',
            userAgentCreated : 'Browser 1'
        ));
        $registry->track(record: new SessionRecord(
            sessionId        : 'session-2',
            userId           : new UserId(value: 1),
            createdAt        : $clock->now()->sub(interval: new DateInterval(duration: 'PT2H')),
            lastSeenAt       : $clock->now()->sub(interval: new DateInterval(duration: 'PT10M')),
            idleExpiresAt    : $clock->now()->add(interval: new DateInterval(duration: 'PT5M')),
            absoluteExpiresAt: $clock->now()->add(interval: new DateInterval(duration: 'PT10H')),
            ipCreated        : '127.0.0.2',
            userAgentCreated : 'Browser 2'
        ));
        $registry->track(record: new SessionRecord(
            sessionId        : 'session-3',
            userId           : new UserId(value: 2),
            createdAt        : $clock->now()->sub(interval: new DateInterval(duration: 'PT1H')),
            lastSeenAt       : $clock->now(),
            idleExpiresAt    : $clock->now()->add(interval: new DateInterval(duration: 'PT15M')),
            absoluteExpiresAt: $clock->now()->add(interval: new DateInterval(duration: 'PT12H'))
        ));

        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(context: AuthenticationContext::authenticated(
            user     : new AuthenticatedUser(id: 1, email: 'user@example.com', username: 'user'),
            mode     : AuthenticationMode::SESSION,
            sessionId: 'session-1'
        ));

        $flow = new ReadActiveSessions(
            currentAuthentication: $currentAuthentication,
            clock                : $clock,
            sessionRegistry      : $registry
        );

        $sessions = $flow->execute();

        $this->assertCount(expectedCount: 2, haystack: $sessions);
        $this->assertSame(expected: 'session-1', actual: $sessions[0]->sessionId);
        $this->assertTrue(condition: $sessions[0]->current);
        $this->assertSame(expected: 'session-2', actual: $sessions[1]->sessionId);
        $this->assertFalse(condition: $sessions[1]->current);
    }

    public function testReadActiveSessionsFailsWhenSessionRegistryIsMissing() : void
    {
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(context: AuthenticationContext::authenticated(
            user: new AuthenticatedUser(id: 1, email: 'user@example.com', username: 'user'),
            mode: AuthenticationMode::SESSION,
            sessionId: 'session-1'
        ));

        $flow = new ReadActiveSessions(
            currentAuthentication: $currentAuthentication,
            clock: new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-12T12:00:00+00:00'))
        );

        $this->expectException(SessionRegistryUnavailable::class);
        $this->expectExceptionMessage('Tracked session management requires a configured session registry.');

        $flow->execute();
    }
}
