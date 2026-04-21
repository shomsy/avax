<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\CheckAuthentication;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\InMemoryAuditLog;
use Avax\Auth\System\Capabilities\Identity\Jwt\JwtIdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Stores\InMemoryMfaStore;
use Avax\Auth\System\Capabilities\Identity\Session\SessionIdentityInterface;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Record\ResolvedToken;
use Avax\Auth\System\Capabilities\Identity\User\User;
use Avax\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Auth\System\Capabilities\Identity\UserSource\UserSourceInterface;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticateRequest;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationRequest;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\ProjectAuthenticatedUser;
use Avax\Auth\System\Flows\VerifyIdentity\EmailVerification\InMemoryEmailVerificationStateStore;
use Avax\Auth\Tests\Support\FrozenClock;
use DateTimeImmutable;
use Mockery;
use Override;
use PHPUnit\Framework\TestCase;

final class AuthenticateRequestTest extends TestCase
{
    public function testAuthenticateRequestUsesConfiguredClockForConflictAuditEvents() : void
    {
        $sessionUser = User::create(
            id          : new UserId(value: 1),
            email       : new UserEmail(value: 'session@example.com'),
            username    : 'session-user',
            passwordHash: 'hash'
        );
        $tokenUser   = User::create(
            id          : new UserId(value: 2),
            email       : new UserEmail(value: 'token@example.com'),
            username    : 'token-user',
            passwordHash: 'hash'
        );

        $userSource = Mockery::mock(UserSourceInterface::class);
        $userSource->shouldReceive('findById')
            ->once()
            ->with(Mockery::on(static fn (UserId $userId) : bool => $userId->value === 1))
            ->andReturn($sessionUser);

        $sessionIdentity = Mockery::mock(SessionIdentityInterface::class);
        $sessionIdentity->shouldReceive('resolveUserId')->once()->andReturn(1);
        $sessionIdentity->shouldReceive('currentSessionId')->once()->andReturn('session-123');
        $sessionIdentity->shouldReceive('resolveMfaVerifiedAt')->once()->andReturn(null);
        $sessionIdentity->shouldReceive('resolvePhishingResistant')->once()->andReturn(false);

        $jwtIdentity = Mockery::mock(JwtIdentityInterface::class);
        $jwtIdentity->shouldReceive('resolve')
            ->once()
            ->with('token-123')
            ->andReturn(new ResolvedToken(
                user     : $tokenUser,
                tokenId  : 'access-123',
                expiresAt: new DateTimeImmutable(datetime: '+1 hour')
            ));

        $auditLog = new InMemoryAuditLog();
        $clock    = new FrozenClock(now: new DateTimeImmutable(datetime: '2026-04-20T12:00:00+00:00'));

        $authenticateRequest = new AuthenticateRequest(
            currentAuthentication   : new CurrentAuthentication(),
            projectAuthenticatedUser: new ProjectAuthenticatedUser(
                emailVerificationState: new InMemoryEmailVerificationStateStore(),
                mfaStore              : new InMemoryMfaStore()
            ),
            userSource              : $userSource,
            auditLog                : $auditLog,
            sessionIdentity         : $sessionIdentity,
            jwtIdentity             : $jwtIdentity,
            clock                   : $clock
        );

        $context = $authenticateRequest->execute(request: new AuthenticationRequest(
            bearerToken : 'token-123',
            ipAddress   : '127.0.0.1',
            userAgent   : 'PHPUnit'
        ));

        $events = $auditLog->events();

        $this->assertFalse(condition: $context->isAuthenticated());
        $this->assertSame(expected: 'credential_conflict', actual: $context->reason());
        $this->assertCount(expectedCount: 1, haystack: $events);
        $this->assertSame(expected: 'auth.ingress.conflict', actual: $events[0]->name);
        $this->assertEquals(expected: $clock->now(), actual: $events[0]->occurredAt);
    }

    #[Override]
    protected function tearDown() : void
    {
        Mockery::close();
    }
}
