<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capabilities\Access;

use Avax\Auth\System\Capabilities\Access\Policy\AccessPolicy;
use Avax\Auth\System\Capabilities\Access\Policy\IdentityPolicyCatalog;
use Avax\Auth\System\Capabilities\Access\RequireAccessPolicy\RequireAccessPolicy;
use Avax\Auth\System\Capabilities\Access\RequireAuthentication\RequireAuthentication;
use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\Access\RequirePermission\PermissionDenied;
use Avax\Auth\System\Capabilities\Access\RequirePermission\RequirePermission;
use Avax\Auth\System\Capabilities\Access\RequirePhishingResistantAuthentication\RequirePhishingResistantAuthentication;
use Avax\Auth\System\Capabilities\Access\RequireResourceOwner\RequireResourceOwner;
use Avax\Auth\System\Capabilities\Access\RequireResourceOwner\ResourceOwnerDenied;
use Avax\Auth\System\Capabilities\Access\RequireRole\RequireRole;
use Avax\Auth\System\Capabilities\Access\RequireRole\RoleDenied;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Models\FreshMfaRequired;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\StepUp\RequireFreshMfa;
use Avax\Auth\System\Capabilities\Identity\User\UserRole;
use Avax\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\RequireAdminElevation\RequireAdminElevation;
use Avax\Auth\System\Capabilities\Tenancy\AdminRealmSupport\AdminElevationRecord;
use Avax\Auth\System\Capabilities\Tenancy\AdminRealmSupport\InMemoryAdminElevationStore;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Foundation\Clock;
use Avax\Tests\TestCase;
use DateMalformedStringException;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class AccessPolicyTest extends TestCase
{
    /**
     * @throws Unauthenticated
     * @throws PermissionDenied
     * @throws RoleDenied
     */
    public function testPolicyStopsWrongResourceOwner() : void
    {
        $current = new CurrentAuthentication();
        $current->store(context: AuthenticationContext::authenticated(
            user     : new AuthenticatedUser(
                           id      : 9,
                           email   : 'owner@example.com',
                           username: 'owner',
                           roles   : [UserRole::USER->value]
                       ),
            mode     : AuthenticationMode::SESSION,
            sessionId: 'session-9'
        ));
        $policy = new RequireAccessPolicy(
            requireAuthentication                 : new RequireAuthentication(currentAuthentication: $current),
            requireRole                           : new RequireRole(currentAuthentication: $current),
            requirePermission                     : new RequirePermission(currentAuthentication: $current),
            requireResourceOwner                  : new RequireResourceOwner(currentAuthentication: $current),
            requirePhishingResistantAuthentication: new RequirePhishingResistantAuthentication(currentAuthentication: $current),
            requireFreshMfa                       : new RequireFreshMfa(currentAuthentication: $current, clock: new Clock()),
            requireAdminElevation                 : new RequireAdminElevation(currentAuthentication: $current, elevationStore: new InMemoryAdminElevationStore(), clock: new Clock())
        );

        $this->expectException(ResourceOwnerDenied::class);
        $policy->execute(policy: new AccessPolicy(resourceOwnerUserId: 11));
    }

    /**
     * @throws Unauthenticated
     * @throws PermissionDenied
     * @throws RoleDenied
     */
    public function testPolicySupportsFreshMfaAndAdminElevation() : void
    {
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
            sessionId        : 'session-admin',
            mfaVerifiedAt    : new DateTimeImmutable(),
            phishingResistant: true
        ));
        $store = new InMemoryAdminElevationStore();
        $store->start(record: new AdminElevationRecord(
                                  userId   : 1,
                                  bindingId: 'session-admin',
                                  expiresAt: new DateTimeImmutable(datetime: '+5 minutes')
                              ));

        $policy = new RequireAccessPolicy(
            requireAuthentication                 : new RequireAuthentication(currentAuthentication: $current),
            requireRole                           : new RequireRole(currentAuthentication: $current),
            requirePermission                     : new RequirePermission(currentAuthentication: $current),
            requireResourceOwner                  : new RequireResourceOwner(currentAuthentication: $current),
            requirePhishingResistantAuthentication: new RequirePhishingResistantAuthentication(currentAuthentication: $current),
            requireFreshMfa                       : new RequireFreshMfa(currentAuthentication: $current, clock: new Clock()),
            requireAdminElevation                 : new RequireAdminElevation(currentAuthentication: $current, elevationStore: $store, clock: new Clock())
        );

        $policy->execute(policy: new AccessPolicy(
                                     requiredRole             : UserRole::ADMIN,
                                     freshMfa                 : true,
                                     adminElevation           : true,
                                     phishingResistantRequired: true
                                 ));

        $this->assertTrue(condition: true);
    }

    /**
     * @throws DateMalformedStringException
     * @throws Unauthenticated
     * @throws PermissionDenied
     * @throws RoleDenied
     */
    public function testAdminIdentityPolicyUsesItsOwnFreshMfaWindow() : void
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
            sessionId        : 'session-admin',
            mfaVerifiedAt    : $clock->now()->modify(modifier: '-4 minutes'),
            phishingResistant: true
        ));
        $store = new InMemoryAdminElevationStore();
        $store->start(record: new AdminElevationRecord(
                                  userId   : 1,
                                  bindingId: 'session-admin',
                                  expiresAt: new DateTimeImmutable(datetime: '+5 minutes')
                              ));

        $policy = new RequireAccessPolicy(
            requireAuthentication                 : new RequireAuthentication(currentAuthentication: $current),
            requireRole                           : new RequireRole(currentAuthentication: $current),
            requirePermission                     : new RequirePermission(currentAuthentication: $current),
            requireResourceOwner                  : new RequireResourceOwner(currentAuthentication: $current),
            requirePhishingResistantAuthentication: new RequirePhishingResistantAuthentication(currentAuthentication: $current),
            requireFreshMfa                       : new RequireFreshMfa(currentAuthentication: $current, clock: $clock),
            requireAdminElevation                 : new RequireAdminElevation(currentAuthentication: $current, elevationStore: $store, clock: $clock)
        );

        $this->expectException(FreshMfaRequired::class);
        $policy->execute(policy: AccessPolicy::forIdentityPolicy(identityPolicy: IdentityPolicyCatalog::admin()));
    }
}
