<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Session;

use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\Session\InMemorySessionRegistry;
use Avax\Auth\System\Capability\Session\SessionRecord;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flow\Session\LogoutAllSessions\LogoutAllSessions;
use Avax\Auth\System\Flow\Token\RefreshTokenStoreInterface;
use Avax\Auth\Tests\Support\FrozenClock;
use DateInterval;
use DateTimeImmutable;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for logout-all session revocation.
 */
final class LogoutAllSessionsTest extends TestCase
{
    public function testLogoutAllSessionsRevokesTrackedSessionsAndClearsContext() : void
    {
        $clock    = new FrozenClock(new DateTimeImmutable('2026-04-12T12:00:00+00:00'));
        $registry = new InMemorySessionRegistry();
        $registry->track(new SessionRecord(
            sessionId        : 'session-1',
            userId           : new UserId(1),
            createdAt        : $clock->now()->sub(new DateInterval('PT30M')),
            lastSeenAt       : $clock->now(),
            idleExpiresAt    : $clock->now()->add(new DateInterval('PT15M')),
            absoluteExpiresAt: $clock->now()->add(new DateInterval('PT12H'))
        ));
        $registry->track(new SessionRecord(
            sessionId        : 'session-2',
            userId           : new UserId(1),
            createdAt        : $clock->now()->sub(new DateInterval('PT1H')),
            lastSeenAt       : $clock->now(),
            idleExpiresAt    : $clock->now()->add(new DateInterval('PT15M')),
            absoluteExpiresAt: $clock->now()->add(new DateInterval('PT12H'))
        ));

        $context = AuthenticationContext::authenticated(
            user     : new AuthenticatedUser(id: 1, email: 'user@example.com', username: 'user'),
            mode     : AuthenticationMode::SESSION,
            sessionId: 'session-1'
        );
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store($context);

        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('clear')->once()->with($context);
        $refreshTokens = Mockery::mock(RefreshTokenStoreInterface::class);
        $refreshTokens->shouldReceive('revokeUser')->once()->with(Mockery::type(UserId::class));

        $flow = new LogoutAllSessions(
            identity             : $identity,
            currentAuthentication: $currentAuthentication,
            auditLog             : new InMemoryAuditLog(),
            clock                : $clock,
            sessionRegistry      : $registry,
            refreshTokenStore    : $refreshTokens
        );

        $flow->execute();

        $this->assertFalse($currentAuthentication->read()->isAuthenticated());
        $this->assertNotNull($registry->find('session-1')?->revokedAt);
        $this->assertNotNull($registry->find('session-2')?->revokedAt);
        $this->assertSame('logout_all', $registry->find('session-1')?->revokeReason);
    }

    protected function tearDown() : void
    {
        Mockery::close();
    }
}
