<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\Provisioning;

use Avax\Auth\System\Capability\AdminRealm\AdminElevationRecord;
use Avax\Auth\System\Capability\AdminRealm\InMemoryAdminElevationStore;
use Avax\Auth\System\Capability\Session\InMemorySessionRegistry;
use Avax\Auth\System\Capability\Session\SessionRecord;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserEmail;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Capability\User\UserRole;
use Avax\Auth\System\Capability\UserSource\InMemoryUserSource;
use Avax\Auth\System\Flow\AdminRealm\RequireAdminElevation\RequireAdminElevation;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flow\Provisioning\DeprovisionUser\DeprovisionUser;
use Avax\Auth\System\Flow\Provisioning\ReactivateUser\ReactivateUser;
use Avax\Auth\System\Flow\Provisioning\SuspendUser\SuspendUser;
use Avax\Auth\System\Flow\Token\InMemoryRefreshTokenStore;
use Avax\Auth\System\Foundation\Clock;
use PHPUnit\Framework\TestCase;

final class ProvisioningTest extends TestCase
{
    public function testSuspendReactivateAndDeprovisionLifecycle() : void
    {
        $clock         = new Clock();
        $userSource    = new InMemoryUserSource();
        $targetUser    = User::create(
            id          : new UserId(2),
            email       : new UserEmail('user@example.com'),
            username    : 'user',
            passwordHash: 'hash',
            roles       : [UserRole::USER]
        );
        $userSource->create($targetUser);

        $current = new CurrentAuthentication();
        $current->store(AuthenticationContext::authenticated(
            user      : new AuthenticatedUser(
                id       : 1,
                email    : 'admin@example.com',
                username : 'admin',
                roles    : [UserRole::ADMIN->value]
            ),
            mode      : AuthenticationMode::SESSION,
            sessionId : 'session-admin'
        ));
        $elevations = new InMemoryAdminElevationStore();
        $elevations->start(new AdminElevationRecord(1, 'session-admin', $clock->now()->modify('+15 minutes')));
        $elevations->start(new AdminElevationRecord(2, 'session-target-suspend', $clock->now()->modify('+15 minutes')));
        $sessionRegistry = new InMemorySessionRegistry();
        $sessionRegistry->track(new SessionRecord(
            sessionId        : 'session-target-suspend',
            userId           : new UserId(2),
            createdAt        : $clock->now(),
            lastSeenAt       : $clock->now(),
            idleExpiresAt    : $clock->now()->modify('+15 minutes'),
            absoluteExpiresAt: $clock->now()->modify('+8 hours')
        ));
        $refreshTokens = new InMemoryRefreshTokenStore();
        $issuedRefresh = $refreshTokens->issue(
            userId    : new UserId(2),
            expiresAt : $clock->now()->modify('+30 days'),
            clientId  : 'oauth-client'
        );

        $guard      = new RequireAdminElevation($current, $elevations, $clock);
        $suspend    = new SuspendUser(
            userSource         : $userSource,
            requireAdminElevation: $guard,
            auditLog           : new InMemoryAuditLog(),
            clock              : $clock,
            sessionRegistry    : $sessionRegistry,
            refreshTokenStore  : $refreshTokens,
            adminElevationStore: $elevations
        );
        $reactivate = new ReactivateUser($userSource, $guard, new InMemoryAuditLog(), $clock);
        $deprovision = new DeprovisionUser(
            userSource         : $userSource,
            requireAdminElevation: $guard,
            auditLog           : new InMemoryAuditLog(),
            clock              : $clock,
            sessionRegistry    : $sessionRegistry,
            refreshTokenStore  : $refreshTokens,
            adminElevationStore: $elevations
        );

        $suspend->execute(2);
        $this->assertFalse($userSource->findById(new UserId(2))?->isActive() ?? true);
        $this->assertTrue($sessionRegistry->find('session-target-suspend')?->isRevoked() ?? false);
        $this->assertSame('suspended', $sessionRegistry->find('session-target-suspend')?->revokeReason);
        $this->assertTrue($refreshTokens->find($issuedRefresh->token)?->revoked ?? false);

        $reactivate->execute(2);
        $this->assertTrue($userSource->findById(new UserId(2))?->isActive() ?? false);

        $sessionRegistry->track(new SessionRecord(
            sessionId        : 'session-target-deprovision',
            userId           : new UserId(2),
            createdAt        : $clock->now(),
            lastSeenAt       : $clock->now(),
            idleExpiresAt    : $clock->now()->modify('+15 minutes'),
            absoluteExpiresAt: $clock->now()->modify('+8 hours')
        ));
        $issuedRefresh = $refreshTokens->issue(
            userId    : new UserId(2),
            expiresAt : $clock->now()->modify('+30 days'),
            clientId  : 'oauth-client-2'
        );
        $elevations->start(new AdminElevationRecord(2, 'session-target-deprovision', $clock->now()->modify('+15 minutes')));

        $deprovision->execute(2);
        $deprovisioned = $userSource->findById(new UserId(2));
        $this->assertFalse($deprovisioned?->isActive() ?? true);
        $this->assertSame([], $deprovisioned?->getRoles() ?? []);
        $this->assertTrue($sessionRegistry->find('session-target-deprovision')?->isRevoked() ?? false);
        $this->assertSame('deprovisioned', $sessionRegistry->find('session-target-deprovision')?->revokeReason);
        $this->assertTrue($refreshTokens->find($issuedRefresh->token)?->revoked ?? false);
        $this->assertNull($elevations->find('session-target-deprovision'));
    }
}
