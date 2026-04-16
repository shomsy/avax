<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Session;

use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\Identity\IdentityInterface;
use Avax\Auth\System\Capability\Session\InMemorySessionRegistry;
use Avax\Auth\System\Capability\Session\SessionRecord;
use Avax\Auth\System\Capability\Session\SessionRegistryUnavailable;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flow\Session\RevokeSession\RevokeSession;
use Avax\Auth\Tests\Support\FrozenClock;
use DateInterval;
use DateInvalidOperationException;
use DateTimeImmutable;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for targeted session revocation.
 */
final class RevokeSessionTest extends TestCase
{
    /**
     * @throws DateInvalidOperationException
     * @throws Unauthenticated
     */
    public function testRevokeSessionRevokesOwnedCurrentSessionAndClearsContext() : void
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

        $context               = AuthenticationContext::authenticated(
            user     : new AuthenticatedUser(id: 1, email: 'user@example.com', username: 'user'),
            mode     : AuthenticationMode::SESSION,
            sessionId: 'session-1'
        );
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(context: $context);

        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('clear')->once()->with($context);

        $flow = new RevokeSession(
            identity             : $identity,
            currentAuthentication: $currentAuthentication,
            auditLog             : new InMemoryAuditLog(),
            clock                : $clock,
            sessionRegistry      : $registry
        );

        $flow->execute(sessionId: 'session-1');

        $this->assertFalse(condition: $currentAuthentication->read()->isAuthenticated());
        $this->assertSame(expected: 'user_revoke', actual: $registry->find(sessionId: 'session-1')?->revokeReason);
    }

    /**
     * @throws Unauthenticated
     */
    public function testRevokeSessionFailsWhenSessionRegistryIsMissing() : void
    {
        $context               = AuthenticationContext::authenticated(
            user     : new AuthenticatedUser(id: 1, email: 'user@example.com', username: 'user'),
            mode     : AuthenticationMode::SESSION,
            sessionId: 'session-1'
        );
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(context: $context);

        $identity = Mockery::mock(IdentityInterface::class);

        $flow = new RevokeSession(
            identity             : $identity,
            currentAuthentication: $currentAuthentication,
            auditLog             : new InMemoryAuditLog(),
            clock                : new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-12T12:00:00+00:00'))
        );

        $this->expectException(SessionRegistryUnavailable::class);
        $this->expectExceptionMessage('Tracked session management requires a configured session registry.');

        $flow->execute(sessionId: 'session-1');
    }

    protected function tearDown() : void
    {
        Mockery::close();
    }
}
