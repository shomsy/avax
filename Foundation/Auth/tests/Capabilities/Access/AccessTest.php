<?php

declare(strict_types=1);

namespace Avax\Auth\Tests\Capability\Access;

use Avax\Auth\System\Capability\Access\Access;
use Avax\Auth\System\Capability\Access\Policy\AccessPolicy;
use Avax\Auth\System\Capability\Access\RequireAccessPolicy\RequireAccessPolicy;
use Avax\Auth\System\Capability\Access\RequireAuthentication\RequireAuthentication;
use Avax\Auth\System\Capability\Access\RequirePhishingResistantAuthentication\RequirePhishingResistantAuthentication;
use Avax\Auth\System\Capability\Access\RequirePermission\RequirePermission;
use Avax\Auth\System\Capability\Access\RequireResourceOwner\RequireResourceOwner;
use Avax\Auth\System\Capability\Access\RequireRole\RequireRole;
use Avax\Auth\System\Capability\AdminRealm\AdminElevationRecord;
use Avax\Auth\System\Capability\AdminRealm\InMemoryAdminElevationStore;
use Avax\Auth\System\Capability\User\UserPermission;
use Avax\Auth\System\Capability\User\UserRole;
use Avax\Auth\System\Flow\AdminRealm\RequireAdminElevation\RequireAdminElevation;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticatedUser;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationContext;
use Avax\Auth\System\Flow\AuthenticateRequest\AuthenticationMode;
use Avax\Auth\System\Flow\AuthenticateRequest\CurrentAuthentication;
use Avax\Auth\System\Flow\Mfa\StepUp\RequireFreshMfa;
use Avax\Auth\System\Foundation\Clock;
use PHPUnit\Framework\TestCase;

/**
 * Unit test for the Access façade.
 */
class AccessTest extends TestCase
{
    public function testAccessFacadeDelegatesToBoundaries() : void
    {
        $currentAuthentication = new CurrentAuthentication();
        $currentAuthentication->store(AuthenticationContext::authenticated(
            user: new AuthenticatedUser(
                      id         : 42,
                      email      : 'access@example.com',
                      username   : 'access',
                      roles      : [UserRole::ADMIN->value],
                      permissions: ['write'],
                      mfaEnabled : true
                  ),
            mode: AuthenticationMode::SESSION,
            sessionId: 'session-42',
            mfaVerifiedAt: new \DateTimeImmutable()
        ));
        $adminElevationStore = new InMemoryAdminElevationStore();
        $adminElevationStore->start(new AdminElevationRecord(
            userId: 42,
            bindingId: 'session-42',
            expiresAt: new \DateTimeImmutable('+10 minutes')
        ));
        $clock = new Clock();
        $requireAuthentication = new RequireAuthentication(currentAuthentication: $currentAuthentication);
        $requireRole = new RequireRole(currentAuthentication: $currentAuthentication);
        $requirePermission = new RequirePermission(currentAuthentication: $currentAuthentication);
        $requirePhishingResistantAuthentication = new RequirePhishingResistantAuthentication($currentAuthentication);

        $access = new Access(
            requireAuthentication: $requireAuthentication,
            requireRole          : $requireRole,
            requirePermission    : $requirePermission,
            requireAccessPolicy  : new RequireAccessPolicy(
                requireAuthentication: $requireAuthentication,
                requireRole          : $requireRole,
                requirePermission    : $requirePermission,
                requireResourceOwner : new RequireResourceOwner($currentAuthentication),
                requirePhishingResistantAuthentication: $requirePhishingResistantAuthentication,
                requireFreshMfa      : new RequireFreshMfa($currentAuthentication, $clock),
                requireAdminElevation: new RequireAdminElevation($currentAuthentication, $adminElevationStore, $clock)
            )
        );

        $access->requireAuthentication();
        $access->requireRole(requiredRole: UserRole::USER);
        $access->requirePermission(permission: new UserPermission(value: 'write'));
        $access->requirePolicy(new AccessPolicy(
            requiredRole      : UserRole::USER,
            requiredPermission: new UserPermission('write'),
            resourceOwnerUserId: 42,
            freshMfa          : true,
            adminElevation    : true
        ));

        $this->assertTrue(condition: true);
    }
}
