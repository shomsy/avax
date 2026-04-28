<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Tests\Flows\Session;

use Avax\Components\Identity\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Capabilities\Diagnostics\Audit\InMemoryAuditLog;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\IdentityInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\InMemorySessionRegistry;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRecord;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRegistryUnavailable;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime\LogoutAllSessions\LogoutAllSessions;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\RefreshTokenStoreInterface;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use Avax\Components\Identity\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Components\Identity\Auth\Tests\Support\FrozenClock;
use Avax\Tests\TestCase;
use DateInterval;
use DateInvalidOperationException;
use DateTimeImmutable;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for logout-all session revocation.
 */
final class LogoutAllSessionsTest extends TestCase
{
    /**
     * @throws DateInvalidOperationException
     * @throws Unauthenticated
     */
    public function testLogoutAllSessionsRevokesTrackedSessionsAndClearsContext() : void
    {
        $clock    = new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-12T12:00:00+00:00'));
        $registry = new InMemorySessionRegistry();
        $registry->track(record: new SessionRecord(
                                     sessionId        : 'session-1',
                                     userId           : new UserId(value: 1),
                                     createdAt        : $clock->now()->sub(interval: new DateInterval(duration: 'PT30M')),
                                     lastSeenAt       : $clock->now(),
                                     idleExpiresAt    : $clock->now()->add(interval: new DateInterval(duration: 'PT15M')),
                                     absoluteExpiresAt: $clock->now()->add(interval: new DateInterval(duration: 'PT12H'))
                                 ));
        $registry->track(record: new SessionRecord(
                                     sessionId        : 'session-2',
                                     userId           : new UserId(value: 1),
                                     createdAt        : $clock->now()->sub(interval: new DateInterval(duration: 'PT1H')),
                                     lastSeenAt       : $clock->now(),
                                     idleExpiresAt    : $clock->now()->add(interval: new DateInterval(duration: 'PT15M')),
                                     absoluteExpiresAt: $clock->now()->add(interval: new DateInterval(duration: 'PT12H'))
                                 ));

        $context               = AuthenticationContext::authenticated(
            user     : new AuthenticatedUser(id: 1, email: 'user@example.com', username: 'user'),
            mode     : AuthenticationMode::SESSION,
            sessionId: 'session-1'
        );
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(context: $context);

        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('clear')->once()->with($context);
        $refreshTokens = Mockery::mock(RefreshTokenStoreInterface::class);
        $refreshTokens->shouldReceive('revokeUser')->once()->with(Mockery::type(expected: UserId::class));

        $flow = new LogoutAllSessions(
            identity             : $identity,
            currentAuthentication: $currentAuthentication,
            auditLog             : new InMemoryAuditLog(),
            clock                : $clock,
            sessionRegistry      : $registry,
            refreshTokenStore    : $refreshTokens
        );

        $flow->execute();

        $this->assertFalse(condition: $currentAuthentication->read()->isAuthenticated());
        $this->assertNotNull(actual: $registry->find(sessionId: 'session-1')?->revokedAt);
        $this->assertNotNull(actual: $registry->find(sessionId: 'session-2')?->revokedAt);
        $this->assertSame(expected: 'logout_all', actual: $registry->find(sessionId: 'session-1')?->revokeReason);
    }

    /**
     * @throws Unauthenticated
     */
    public function testLogoutAllSessionsFailsWhenSessionRegistryIsMissing() : void
    {
        $context               = AuthenticationContext::authenticated(
            user     : new AuthenticatedUser(id: 1, email: 'user@example.com', username: 'user'),
            mode     : AuthenticationMode::SESSION,
            sessionId: 'session-1'
        );
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(context: $context);

        $identity = Mockery::mock(IdentityInterface::class);

        $flow = new LogoutAllSessions(
            identity             : $identity,
            currentAuthentication: $currentAuthentication,
            auditLog             : new InMemoryAuditLog(),
            clock                : new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-12T12:00:00+00:00'))
        );

        $this->expectException(SessionRegistryUnavailable::class);
        $this->expectExceptionMessage('Tracked session management requires a configured session registry.');

        $flow->execute();
    }

    protected function tearDown() : void
    {
        Mockery::close();
    }
}
