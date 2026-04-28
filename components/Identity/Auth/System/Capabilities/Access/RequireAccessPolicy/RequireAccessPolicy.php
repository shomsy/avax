<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Access\RequireAccessPolicy;

use Avax\Components\Identity\Auth\System\Capabilities\Access\Policy\AccessPolicy;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RequireAuthentication\RequireAuthentication;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RequirePermission\PermissionDenied;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RequirePermission\RequirePermission;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RequirePhishingResistantAuthentication\PhishingResistantAuthenticationRequired;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RequirePhishingResistantAuthentication\RequirePhishingResistantAuthentication;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RequireResourceOwner\RequireResourceOwner;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RequireResourceOwner\ResourceOwnerDenied;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RequireRole\RequireRole;
use Avax\Components\Identity\Auth\System\Capabilities\Access\RequireRole\RoleDenied;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\Models\FreshMfaRequired;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\Mfa\Runtime\StepUp\RequireFreshMfa;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\AdminElevationFailed;
use Avax\Components\Identity\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\RequireAdminElevation\RequireAdminElevation;
use SensitiveParameter;

/**
 * Evaluates composed authorization requirements in one place.
 */
final readonly class RequireAccessPolicy
{
    public function __construct(
        #[SensitiveParameter] private RequireAuthentication                  $requireAuthentication,
        private RequireRole                                                  $requireRole,
        private RequirePermission                                            $requirePermission,
        private RequireResourceOwner                                         $requireResourceOwner,
        #[SensitiveParameter] private RequirePhishingResistantAuthentication $requirePhishingResistantAuthentication,
        private RequireFreshMfa                                              $requireFreshMfa,
        private RequireAdminElevation                                        $requireAdminElevation
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
