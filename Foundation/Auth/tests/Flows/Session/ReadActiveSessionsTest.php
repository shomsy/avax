<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Session;

use Avax\Auth\System\Capability\Session\InMemorySessionRegistry;
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
    public function testReadActiveSessionsReturnsCurrentUsersTrackedSessions() : void
    {
        $clock    = new FrozenClock(new DateTimeImmutable('2026-04-12T12:00:00+00:00'));
        $registry = new InMemorySessionRegistry();
        $registry->track(new SessionRecord(
            sessionId        : 'session-1',
            userId           : new UserId(1),
            createdAt        : $clock->now()->sub(new DateInterval('PT30M')),
            lastSeenAt       : $clock->now(),
            idleExpiresAt    : $clock->now()->add(new DateInterval('PT15M')),
            absoluteExpiresAt: $clock->now()->add(new DateInterval('PT12H')),
            ipCreated        : '127.0.0.1',
            userAgentCreated : 'Browser 1'
        ));
        $registry->track(new SessionRecord(
            sessionId        : 'session-2',
            userId           : new UserId(1),
            createdAt        : $clock->now()->sub(new DateInterval('PT2H')),
            lastSeenAt       : $clock->now()->sub(new DateInterval('PT10M')),
            idleExpiresAt    : $clock->now()->add(new DateInterval('PT5M')),
            absoluteExpiresAt: $clock->now()->add(new DateInterval('PT10H')),
            ipCreated        : '127.0.0.2',
            userAgentCreated : 'Browser 2'
        ));
        $registry->track(new SessionRecord(
            sessionId        : 'session-3',
            userId           : new UserId(2),
            createdAt        : $clock->now()->sub(new DateInterval('PT1H')),
            lastSeenAt       : $clock->now(),
            idleExpiresAt    : $clock->now()->add(new DateInterval('PT15M')),
            absoluteExpiresAt: $clock->now()->add(new DateInterval('PT12H'))
        ));

        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(AuthenticationContext::authenticated(
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

        $this->assertCount(2, $sessions);
        $this->assertSame('session-1', $sessions[0]->sessionId);
        $this->assertTrue($sessions[0]->current);
        $this->assertSame('session-2', $sessions[1]->sessionId);
        $this->assertFalse($sessions[1]->current);
    }
}
