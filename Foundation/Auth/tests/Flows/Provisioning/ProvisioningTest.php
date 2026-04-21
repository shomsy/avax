<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\Provisioning;

use Avax\Auth\System\Capabilities\Diagnostics\Audit\InMemoryAuditLog;
use Avax\Auth\System\Capabilities\Identity\Sessions\Registry\InMemorySessionRegistry;
use Avax\Auth\System\Capabilities\Identity\Sessions\Registry\SessionRecord;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\InMemoryRefreshTokenStore;
use Avax\Auth\System\Capabilities\Identity\Tokens\Runtime\Store\InMemoryRefreshTokenStore;
use Avax\Auth\System\Capabilities\Identity\User\User;
use Avax\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Auth\System\Capabilities\Identity\User\UserRole;
use Avax\Auth\System\Capabilities\Identity\UserSource\InMemoryUserSource;
use Avax\Auth\System\Capabilities\IdentitySync\Lifecycle\InMemoryLifecycleStore;
use Avax\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleOrchestrator;
use Avax\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleSource;
use Avax\Auth\System\Capabilities\IdentitySync\Lifecycle\LifecycleState;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\ProvisioningRuntime\DeprovisionUser\DeprovisionUser;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\ProvisioningRuntime\ReactivateUser\ReactivateUser;
use Avax\Auth\System\Capabilities\IdentitySync\SCIM\ProvisioningRuntime\SuspendUser\SuspendUser;
use Avax\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\RequireAdminElevation\RequireAdminElevation;
use Avax\Auth\System\Capabilities\Tenancy\AdminRealmSupport\AdminElevationRecord;
use Avax\Auth\System\Capabilities\Tenancy\AdminRealmSupport\InMemoryAdminElevationStore;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Foundation\Clock;
use DateMalformedStringException;
use PHPUnit\Framework\TestCase;
use Random\RandomException;

final class ProvisioningTest extends TestCase
{
    /**
     * @throws RandomException
     * @throws DateMalformedStringException
     */
    public function testSuspendReactivateAndDeprovisionLifecycle() : void
    {
        $clock      = new Clock();
        $userSource = new InMemoryUserSource();
        $targetUser = User::create(
            id          : new UserId(value: 2),
            email       : new UserEmail(value: 'user@example.com'),
            username    : 'user',
            passwordHash: 'hash',
            roles       : [UserRole::USER]
        );
        $userSource->create(user: $targetUser);

        $current = new CurrentAuthentication();
        $current->store(context: AuthenticationContext::authenticated(
            user     : new AuthenticatedUser(
                           id      : 1,
                           email   : 'admin@example.com',
                           username: 'admin',
                           roles   : [UserRole::ADMIN->value]
                       ),
            mode     : AuthenticationMode::SESSION,
            sessionId: 'session-admin'
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
        $refreshTokens  = new InMemoryRefreshTokenStore();
        $issuedRefresh  = $refreshTokens->issue(
            userId   : new UserId(value: 2),
            expiresAt: $clock->now()->modify(modifier: '+30 days'),
            clientId : 'oauth-client'
        );
        $lifecycleStore = new InMemoryLifecycleStore();
        $lifecycle      = new LifecycleOrchestrator(
            userSource: $userSource,
            store     : $lifecycleStore,
            auditLog  : new InMemoryAuditLog(),
            clock     : $clock
        );

        $guard       = new RequireAdminElevation(currentAuthentication: $current, elevationStore: $elevations, clock: $clock);
        $suspend     = new SuspendUser(
            userSource           : $userSource,
            requireAdminElevation: $guard,
            auditLog             : new InMemoryAuditLog(),
            clock                : $clock,
            sessionRegistry      : $sessionRegistry,
            refreshTokenStore    : $refreshTokens,
            adminElevationStore  : $elevations,
            lifecycle            : $lifecycle
        );
        $reactivate  = new ReactivateUser(
            userSource           : $userSource,
            requireAdminElevation: $guard,
            auditLog             : new InMemoryAuditLog(),
            clock                : $clock,
            lifecycle            : $lifecycle
        );
        $deprovision = new DeprovisionUser(
            userSource           : $userSource,
            requireAdminElevation: $guard,
            auditLog             : new InMemoryAuditLog(),
            clock                : $clock,
            sessionRegistry      : $sessionRegistry,
            refreshTokenStore    : $refreshTokens,
            adminElevationStore  : $elevations,
            lifecycle            : $lifecycle
        );

        $suspend->execute(userId: 2);
        $this->assertFalse(condition: $userSource->findById(id: new UserId(value: 2))?->isActive() ?? true);
        $this->assertSame(expected: LifecycleState::SUSPENDED, actual: $lifecycleStore->find(userId: new UserId(value: 2))?->state);
        $this->assertSame(expected: LifecycleSource::ADMIN, actual: $lifecycleStore->find(userId: new UserId(value: 2))?->source);
        $this->assertTrue(condition: $sessionRegistry->find(sessionId: 'session-target-suspend')?->isRevoked() ?? false);
        $this->assertSame(expected: 'suspended', actual: $sessionRegistry->find(sessionId: 'session-target-suspend')?->revokeReason);
        $this->assertTrue(condition: $refreshTokens->find(plainToken: $issuedRefresh->token)?->revoked ?? false);

        $reactivate->execute(userId: 2);
        $this->assertTrue(condition: $userSource->findById(id: new UserId(value: 2))?->isActive() ?? false);
        $this->assertSame(expected: LifecycleState::ACTIVE, actual: $lifecycleStore->find(userId: new UserId(value: 2))?->state);

        $sessionRegistry->track(record: new SessionRecord(
                                            sessionId        : 'session-target-deprovision',
                                            userId           : new UserId(value: 2),
                                            createdAt        : $clock->now(),
                                            lastSeenAt       : $clock->now(),
                                            idleExpiresAt    : $clock->now()->modify(modifier: '+15 minutes'),
                                            absoluteExpiresAt: $clock->now()->modify(modifier: '+8 hours')
                                        ));
        $issuedRefresh = $refreshTokens->issue(
            userId   : new UserId(value: 2),
            expiresAt: $clock->now()->modify(modifier: '+30 days'),
            clientId : 'oauth-client-2'
        );
        $elevations->start(record: new AdminElevationRecord(userId: 2, bindingId: 'session-target-deprovision', expiresAt: $clock->now()->modify(modifier: '+15 minutes')));

        $deprovision->execute(userId: 2);
        $deprovisioned = $userSource->findById(id: new UserId(value: 2));
        $this->assertFalse(condition: $deprovisioned?->isActive() ?? true);
        $this->assertSame(expected: [], actual: $deprovisioned?->getRoles() ?? []);
        $this->assertSame(expected: LifecycleState::DEPROVISIONED, actual: $lifecycleStore->find(userId: new UserId(value: 2))?->state);
        $this->assertTrue(condition: $sessionRegistry->find(sessionId: 'session-target-deprovision')?->isRevoked() ?? false);
        $this->assertSame(expected: 'deprovisioned', actual: $sessionRegistry->find(sessionId: 'session-target-deprovision')?->revokeReason);
        $this->assertTrue(condition: $refreshTokens->find(plainToken: $issuedRefresh->token)?->revoked ?? false);
        $this->assertNull(actual: $elevations->find(bindingId: 'session-target-deprovision'));
    }
}
