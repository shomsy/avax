<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\Access;

use Avax\Auth\System\Capability\Access\Policy\AccessPolicy;
use Avax\Auth\System\Capability\Access\RequireAccessPolicy\RequireAccessPolicy;
use Avax\Auth\System\Capability\Access\RequireAuthentication\RequireAuthentication;
use Avax\Auth\System\Capability\Access\RequirePhishingResistantAuthentication\RequirePhishingResistantAuthentication;
use Avax\Auth\System\Capability\Access\RequirePermission\RequirePermission;
use Avax\Auth\System\Capability\Access\RequireResourceOwner\RequireResourceOwner;
use Avax\Auth\System\Capability\Access\RequireResourceOwner\ResourceOwnerDenied;
use Avax\Auth\System\Capability\Access\RequireRole\RequireRole;
use Avax\Auth\System\Capability\AdminRealm\AdminElevationRecord;
use Avax\Auth\System\Capability\AdminRealm\InMemoryAdminElevationStore;
use Avax\Auth\System\Capability\User\UserRole;
use Avax\Auth\System\Flow\AdminRealm\RequireAdminElevation\RequireAdminElevation;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Mfa\StepUp\RequireFreshMfa;
use Avax\Auth\System\Foundation\Clock;
use PHPUnit\Framework\TestCase;

final class AccessPolicyTest extends TestCase
{
    public function testPolicyStopsWrongResourceOwner() : void
    {
        $current = new CurrentAuthentication();
        $current->store(AuthenticationContext::authenticated(
            user      : new AuthenticatedUser(
                id       : 9,
                email    : 'owner@example.com',
                username : 'owner',
                roles    : [UserRole::USER->value]
            ),
            mode      : AuthenticationMode::SESSION,
            sessionId : 'session-9'
        ));
        $policy = new RequireAccessPolicy(
            requireAuthentication: new RequireAuthentication($current),
            requireRole          : new RequireRole($current),
            requirePermission    : new RequirePermission($current),
            requireResourceOwner : new RequireResourceOwner($current),
            requirePhishingResistantAuthentication: new RequirePhishingResistantAuthentication($current),
            requireFreshMfa      : new RequireFreshMfa($current, new Clock()),
            requireAdminElevation: new RequireAdminElevation($current, new InMemoryAdminElevationStore(), new Clock())
        );

        $this->expectException(ResourceOwnerDenied::class);
        $policy->execute(new AccessPolicy(resourceOwnerUserId: 11));
    }

    public function testPolicySupportsFreshMfaAndAdminElevation() : void
    {
        $current = new CurrentAuthentication();
        $current->store(AuthenticationContext::authenticated(
            user         : new AuthenticatedUser(
                id         : 1,
                email      : 'admin@example.com',
                username   : 'admin',
                roles      : [UserRole::ADMIN->value],
                mfaEnabled : true
            ),
            mode         : AuthenticationMode::SESSION,
            sessionId    : 'session-admin',
            mfaVerifiedAt: new \DateTimeImmutable(),
            phishingResistant: true
        ));
        $store = new InMemoryAdminElevationStore();
        $store->start(new AdminElevationRecord(
            userId    : 1,
            bindingId : 'session-admin',
            expiresAt : new \DateTimeImmutable('+5 minutes')
        ));

        $policy = new RequireAccessPolicy(
            requireAuthentication: new RequireAuthentication($current),
            requireRole          : new RequireRole($current),
            requirePermission    : new RequirePermission($current),
            requireResourceOwner : new RequireResourceOwner($current),
            requirePhishingResistantAuthentication: new RequirePhishingResistantAuthentication($current),
            requireFreshMfa      : new RequireFreshMfa($current, new Clock()),
            requireAdminElevation: new RequireAdminElevation($current, $store, new Clock())
        );

        $policy->execute(new AccessPolicy(
            requiredRole   : UserRole::ADMIN,
            phishingResistant: true,
            freshMfa       : true,
            adminElevation : true
        ));

        $this->assertTrue(true);
    }
}
