<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flow\AdminRealm;

use Avax\Auth\System\Capability\AdminRealm\InMemoryAdminElevationStore;
use Avax\Auth\System\Capability\User\UserRole;
use Avax\Auth\System\Flow\AdminRealm\AdminElevationFailed;
use Avax\Auth\System\Flow\AdminRealm\BeginAdminElevation\BeginAdminElevation;
use Avax\Auth\System\Flow\AdminRealm\EndAdminElevation\EndAdminElevation;
use Avax\Auth\System\Flow\AdminRealm\RequireAdminElevation\RequireAdminElevation;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Diagnostics\InMemoryAuditLog;
use Avax\Auth\System\Flow\Mfa\StepUp\RequireFreshMfa;
use Avax\Auth\System\Foundation\Clock;
use PHPUnit\Framework\TestCase;

final class AdminElevationTest extends TestCase
{
    public function testAdminElevationLifecycle() : void
    {
        $clock = new Clock();
        $current = new CurrentAuthentication();
        $current->store(context: AuthenticationContext::authenticated(
            user         : new AuthenticatedUser(
                id        : 1,
                email     : 'admin@example.com',
                username  : 'admin',
                roles     : [UserRole::ADMIN->value],
                mfaEnabled: true
            ),
            mode         : AuthenticationMode::SESSION,
            sessionId    : 'session-1',
            mfaVerifiedAt: $clock->now(),
            phishingResistant: true
        ));

        $store = new InMemoryAdminElevationStore();
        $begin = new BeginAdminElevation(
            currentAuthentication: $current,
            requireFreshMfa      : new RequireFreshMfa(currentAuthentication: $current, clock: $clock),
            elevationStore       : $store,
            auditLog             : new InMemoryAuditLog(),
            clock                : $clock,
            phishingResistantRequired: true
        );
        $guard = new RequireAdminElevation(currentAuthentication: $current, elevationStore: $store, clock: $clock);
        $end   = new EndAdminElevation(currentAuthentication: $current, elevationStore: $store, auditLog: new InMemoryAuditLog(), clock: $clock);

        $elevation = $begin->execute();
        $this->assertSame(expected: 'session-1', actual: $elevation->bindingId);

        $guard->execute();
        $end->execute();

        $this->expectException(AdminElevationFailed::class);
        $guard->execute();
    }

    public function testAdminElevationRequiresPhishingResistantAuthenticationWhenConfigured() : void
    {
        $clock = new Clock();
        $current = new CurrentAuthentication();
        $current->store(context: AuthenticationContext::authenticated(
            user         : new AuthenticatedUser(
                id        : 1,
                email     : 'admin@example.com',
                username  : 'admin',
                roles     : [UserRole::ADMIN->value],
                mfaEnabled: true
            ),
            mode         : AuthenticationMode::SESSION,
            sessionId    : 'session-2',
            mfaVerifiedAt: $clock->now()
        ));

        $begin = new BeginAdminElevation(
            currentAuthentication: $current,
            requireFreshMfa      : new RequireFreshMfa(currentAuthentication: $current, clock: $clock),
            elevationStore       : new InMemoryAdminElevationStore(),
            auditLog             : new InMemoryAuditLog(),
            clock                : $clock,
            phishingResistantRequired: true
        );

        $this->expectException(AdminElevationFailed::class);
        $this->expectExceptionMessage('Admin elevation requires phishing-resistant authentication.');

        $begin->execute();
    }
}
