<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\Access;

use Avax\Auth\System\Capability\Access\Policy\AccessPolicy;
use Avax\Auth\System\Capability\Access\Policy\IdentityPolicyCatalog;
use Avax\Auth\System\Capability\Access\RequireAccessPolicy\RequireAccessPolicy;
use Avax\Auth\System\Capability\Access\RequireAuthentication\RequireAuthentication;
use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\Access\RequirePermission\PermissionDenied;
use Avax\Auth\System\Capability\Access\RequirePermission\RequirePermission;
use Avax\Auth\System\Capability\Access\RequirePhishingResistantAuthentication\RequirePhishingResistantAuthentication;
use Avax\Auth\System\Capability\Access\RequireResourceOwner\RequireResourceOwner;
use Avax\Auth\System\Capability\Access\RequireResourceOwner\ResourceOwnerDenied;
use Avax\Auth\System\Capability\Access\RequireRole\RequireRole;
use Avax\Auth\System\Capability\Access\RequireRole\RoleDenied;
use Avax\Auth\System\Capability\AdminRealm\AdminElevationRecord;
use Avax\Auth\System\Capability\AdminRealm\InMemoryAdminElevationStore;
use Avax\Auth\System\Capability\User\UserRole;
use Avax\Auth\System\Flow\AdminRealm\RequireAdminElevation\RequireAdminElevation;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Mfa\FreshMfaRequired;
use Avax\Auth\System\Flow\Mfa\StepUp\RequireFreshMfa;
use Avax\Auth\System\Foundation\Clock;
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
     * @throws \DateMalformedStringException
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
