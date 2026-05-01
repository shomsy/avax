<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\RequireAccessPolicy;

use Avax\Components\Identity\Access\System\Capabilities\Policy\AccessPolicy;
use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\RequireAuthentication;
use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Access\System\Capabilities\RequirePermission\PermissionDenied;
use Avax\Components\Identity\Access\System\Capabilities\RequirePermission\RequirePermission;
use Avax\Components\Identity\Access\System\Capabilities\RequirePhishingResistantAuthentication\PhishingResistantAuthenticationRequired;
use Avax\Components\Identity\Access\System\Capabilities\RequirePhishingResistantAuthentication\RequirePhishingResistantAuthentication;
use Avax\Components\Identity\Access\System\Capabilities\RequireResourceOwner\RequireResourceOwner;
use Avax\Components\Identity\Access\System\Capabilities\RequireResourceOwner\ResourceOwnerDenied;
use Avax\Components\Identity\Access\System\Capabilities\RequireRole\RequireRole;
use Avax\Components\Identity\Access\System\Capabilities\RequireRole\RoleDenied;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Models\FreshMfaRequired;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\StepUp\RequireFreshMfa;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\AdminElevationFailed;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\RequireAdminElevation\RequireAdminElevation;
use SensitiveParameter;

/**
 * Evaluates composed authorization requirements in one place.
 */
final readonly class RequireAccessPolicy
{
    public function __construct(
        #[SensitiveParameter]
        private RequireAuthentication $requireAuthentication,
        private RequireRole $requireRole,
        private RequirePermission $requirePermission,
        private RequireResourceOwner $requireResourceOwner,
        #[SensitiveParameter]
        private RequirePhishingResistantAuthentication $requirePhishingResistantAuthentication,
        private RequireFreshMfa $requireFreshMfa,
        private RequireAdminElevation $requireAdminElevation,
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
    public function execute(AccessPolicy $policy): void
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
