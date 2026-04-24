<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Flows\AdminRealm;

use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\Diagnostics\Audit\InMemoryAuditLog;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\StepUp\RequireFreshMfa;
use Avax\Auth\System\Capabilities\Identity\User\UserRole;
use Avax\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\AdminElevationFailed;
use Avax\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\BeginAdminElevation\BeginAdminElevation;
use Avax\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\EndAdminElevation\EndAdminElevation;
use Avax\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\RequireAdminElevation\RequireAdminElevation;
use Avax\Auth\System\Capabilities\Tenancy\AdminRealmSupport\InMemoryAdminElevationStore;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Foundation\Clock;
use Avax\Tests\TestCase;
use DateMalformedStringException;
use PHPUnit\Framework\TestCase;

final class AdminElevationTest extends TestCase
{
    /**
     * @throws Unauthenticated
     * @throws DateMalformedStringException
     */
    public function testAdminElevationLifecycle() : void
    {
        $clock   = new Clock();
        $current = new CurrentAuthentication();
        $current->store(context: AuthenticationContext::authenticated(
            user             : new AuthenticatedUser(
                                   id        : 1,
                                   email     : 'admin@example.com',
                                   username  : 'admin',
                                   roles     : [UserRole::ADMIN->value],
                                   mfaEnabled: true
                               ),
            mode             : AuthenticationMode::SESSION,
            sessionId        : 'session-1',
            mfaVerifiedAt    : $clock->now(),
            phishingResistant: true
        ));

        $store = new InMemoryAdminElevationStore();
        $begin = new BeginAdminElevation(
            currentAuthentication    : $current,
            requireFreshMfa          : new RequireFreshMfa(currentAuthentication: $current, clock: $clock),
            elevationStore           : $store,
            auditLog                 : new InMemoryAuditLog(),
            clock                    : $clock,
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

    /**
     * @throws Unauthenticated
     * @throws DateMalformedStringException
     */
    public function testAdminElevationRequiresPhishingResistantAuthenticationWhenConfigured() : void
    {
        $clock   = new Clock();
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
            currentAuthentication    : $current,
            requireFreshMfa          : new RequireFreshMfa(currentAuthentication: $current, clock: $clock),
            elevationStore           : new InMemoryAdminElevationStore(),
            auditLog                 : new InMemoryAuditLog(),
            clock                    : $clock,
            phishingResistantRequired: true
        );

        $this->expectException(AdminElevationFailed::class);
        $this->expectExceptionMessage('Admin elevation requires phishing-resistant authentication.');

        $begin->execute();
    }
}
