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
            id          : new UserId(value: 2),
            email       : new UserEmail(value: 'user@example.com'),
            username    : 'user',
            passwordHash: 'hash',
            roles       : [UserRole::USER]
        );
        $userSource->create(user: $targetUser);

        $current = new CurrentAuthentication();
        $current->store(context: AuthenticationContext::authenticated(
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
        $elevations->start(record: new AdminElevationRecord(userId: 1, bindingId: 'session-admin', expiresAt: $clock->now()->modify(modifier: '+15 minutes')));
        $elevations->start(record: new AdminElevationRecord(userId: 2, bindingId: 'session-target-suspend', expiresAt: $clock->now()->modify(modifier: '+15 minutes')));
        $sessionRegistry = new InMemorySessionRegistry();
        $sessionRegistry->track(record: new SessionRecord(
            sessionId        : 'session-target-suspend',
            userId           : new UserId(value: 2),
            createdAt        : $clock->now(),
            lastSeenAt       : $clock->now(),
            idleExpiresAt    : $clock->now()->modify(modifier: '+15 minutes'),
            absoluteExpiresAt: $clock->now()->modify(modifier: '+8 hours')
        ));
        $refreshTokens = new InMemoryRefreshTokenStore();
        $issuedRefresh = $refreshTokens->issue(
            userId    : new UserId(value: 2),
            expiresAt : $clock->now()->modify(modifier: '+30 days'),
            clientId  : 'oauth-client'
        );

        $guard      = new RequireAdminElevation(currentAuthentication: $current, elevationStore: $elevations, clock: $clock);
        $suspend    = new SuspendUser(
            userSource         : $userSource,
            requireAdminElevation: $guard,
            auditLog           : new InMemoryAuditLog(),
            clock              : $clock,
            sessionRegistry    : $sessionRegistry,
            refreshTokenStore  : $refreshTokens,
            adminElevationStore: $elevations
        );
        $reactivate = new ReactivateUser(userSource: $userSource, requireAdminElevation: $guard, auditLog: new InMemoryAuditLog(), clock: $clock);
        $deprovision = new DeprovisionUser(
            userSource         : $userSource,
            requireAdminElevation: $guard,
            auditLog           : new InMemoryAuditLog(),
            clock              : $clock,
            sessionRegistry    : $sessionRegistry,
            refreshTokenStore  : $refreshTokens,
            adminElevationStore: $elevations
        );

        $suspend->execute(userId: 2);
        $this->assertFalse(condition: $userSource->findById(id: new UserId(value: 2))?->isActive() ?? true);
        $this->assertTrue(condition: $sessionRegistry->find(sessionId: 'session-target-suspend')?->isRevoked() ?? false);
        $this->assertSame(expected: 'suspended', actual: $sessionRegistry->find(sessionId: 'session-target-suspend')?->revokeReason);
        $this->assertTrue(condition: $refreshTokens->find(plainToken: $issuedRefresh->token)?->revoked ?? false);

        $reactivate->execute(userId: 2);
        $this->assertTrue(condition: $userSource->findById(id: new UserId(value: 2))?->isActive() ?? false);

        $sessionRegistry->track(record: new SessionRecord(
            sessionId        : 'session-target-deprovision',
            userId           : new UserId(value: 2),
            createdAt        : $clock->now(),
            lastSeenAt       : $clock->now(),
            idleExpiresAt    : $clock->now()->modify(modifier: '+15 minutes'),
            absoluteExpiresAt: $clock->now()->modify(modifier: '+8 hours')
        ));
        $issuedRefresh = $refreshTokens->issue(
            userId    : new UserId(value: 2),
            expiresAt : $clock->now()->modify(modifier: '+30 days'),
            clientId  : 'oauth-client-2'
        );
        $elevations->start(record: new AdminElevationRecord(userId: 2, bindingId: 'session-target-deprovision', expiresAt: $clock->now()->modify(modifier: '+15 minutes')));

        $deprovision->execute(userId: 2);
        $deprovisioned = $userSource->findById(id: new UserId(value: 2));
        $this->assertFalse(condition: $deprovisioned?->isActive() ?? true);
        $this->assertSame(expected: [], actual: $deprovisioned?->getRoles() ?? []);
        $this->assertTrue(condition: $sessionRegistry->find(sessionId: 'session-target-deprovision')?->isRevoked() ?? false);
        $this->assertSame(expected: 'deprovisioned', actual: $sessionRegistry->find(sessionId: 'session-target-deprovision')?->revokeReason);
        $this->assertTrue(condition: $refreshTokens->find(plainToken: $issuedRefresh->token)?->revoked ?? false);
        $this->assertNull(actual: $elevations->find(bindingId: 'session-target-deprovision'));
    }
}
