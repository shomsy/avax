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
use Avax\Auth\System\Flow\Session\RevokeSession\RevokeSession;
use Avax\Auth\Tests\Support\FrozenClock;
use DateInterval;
use DateTimeImmutable;
use Mockery;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for targeted session revocation.
 */
final class RevokeSessionTest extends TestCase
{
    public function testRevokeSessionRevokesOwnedCurrentSessionAndClearsContext() : void
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

        $context = AuthenticationContext::authenticated(
            user     : new AuthenticatedUser(id: 1, email: 'user@example.com', username: 'user'),
            mode     : AuthenticationMode::SESSION,
            sessionId: 'session-1'
        );
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store($context);

        $identity = Mockery::mock(IdentityInterface::class);
        $identity->shouldReceive('clear')->once()->with($context);

        $flow = new RevokeSession(
            identity             : $identity,
            currentAuthentication: $currentAuthentication,
            auditLog             : new InMemoryAuditLog(),
            clock                : $clock,
            sessionRegistry      : $registry
        );

        $flow->execute('session-1');

        $this->assertFalse($currentAuthentication->read()->isAuthenticated());
        $this->assertSame('user_revoke', $registry->find('session-1')?->revokeReason);
    }

    protected function tearDown() : void
    {
        Mockery::close();
    }
}
