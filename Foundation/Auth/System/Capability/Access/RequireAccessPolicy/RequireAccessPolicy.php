<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Access\RequireAccessPolicy;

use Avax\Auth\System\Capability\Access\Policy\AccessPolicy;
use Avax\Auth\System\Capability\Access\RequirePhishingResistantAuthentication\PhishingResistantAuthenticationRequired;
use Avax\Auth\System\Capability\Access\RequirePhishingResistantAuthentication\RequirePhishingResistantAuthentication;
use Avax\Auth\System\Capability\Access\RequireAuthentication\RequireAuthentication;
use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\Access\RequirePermission\PermissionDenied;
use Avax\Auth\System\Capability\Access\RequirePermission\RequirePermission;
use Avax\Auth\System\Capability\Access\RequireResourceOwner\RequireResourceOwner;
use Avax\Auth\System\Capability\Access\RequireResourceOwner\ResourceOwnerDenied;
use Avax\Auth\System\Capability\Access\RequireRole\RequireRole;
use Avax\Auth\System\Capability\Access\RequireRole\RoleDenied;
use Avax\Auth\System\Flow\AdminRealm\AdminElevationFailed;
use Avax\Auth\System\Flow\AdminRealm\RequireAdminElevation\RequireAdminElevation;
use Avax\Auth\System\Flow\Mfa\FreshMfaRequired;
use Avax\Auth\System\Flow\Mfa\StepUp\RequireFreshMfa;

/**
 * Evaluates composed authorization requirements in one place.
 */
final readonly class RequireAccessPolicy
{
    public function __construct(
        #[\SensitiveParameter] private RequireAuthentication                  $requireAuthentication,
        private RequireRole                                                   $requireRole,
        private RequirePermission                                             $requirePermission,
        private RequireResourceOwner                                          $requireResourceOwner,
        #[\SensitiveParameter] private RequirePhishingResistantAuthentication $requirePhishingResistantAuthentication,
        private RequireFreshMfa                                               $requireFreshMfa,
        private RequireAdminElevation                                         $requireAdminElevation
    ) {}

    /**
     * @throws AdminElevationFailed
     * @throws FreshMfaRequired
     * @throws PermissionDenied
     * @throws PhishingResistantAuthenticationRequired
     * @throws ResourceOwnerDenied
     * @throws RoleDenied
     * @throws Unauthenticated
     */
    public function execute(AccessPolicy $policy) : void
    {
        $this->requireAuthentication->execute();
        $identityPolicy = $policy->identityPolicy;

        if ($policy->requiredRole !== null) {
            $this->requireRole->execute(requiredRole: $policy->requiredRole);
        }

        if ($policy->requiredPermission !== null) {
            $this->requirePermission->execute(permission: $policy->requiredPermission);
        }

        if ($policy->resourceOwnerUserId !== null) {
            $this->requireResourceOwner->execute(ownerUserId: $policy->resourceOwnerUserId);
        }

        if ($policy->phishingResistantRequired || $identityPolicy?->phishingResistantRequired === true) {
            $this->requirePhishingResistantAuthentication->execute();
        }

        $freshMfaMaxAgeSeconds = $policy->freshMfaMaxAgeSeconds
            ?? $identityPolicy?->freshMfaMaxAgeSeconds;

        if ($policy->freshMfa || $freshMfaMaxAgeSeconds !== null) {
            $this->requireFreshMfa->execute(maxAgeSeconds: $freshMfaMaxAgeSeconds);
        }

        if ($policy->adminElevation || $identityPolicy?->adminElevationRequired === true) {
            $this->requireAdminElevation->execute();
        }
    }
}
