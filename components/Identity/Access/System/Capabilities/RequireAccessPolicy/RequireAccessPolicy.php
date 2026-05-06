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
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserPermission;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserRole;
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
    ) {
    }

    /**
     * @throws AdminElevationFailed
     * @throws FreshMfaRequired
     * @throws PermissionDenied
     * @throws PhishingResistantAuthenticationRequired
     * @throws ResourceOwnerDenied
     * @throws RoleDenied
     * @throws Unauthenticated
     */
    public function execute(AccessPolicy $accessPolicy): void
    {
        $this->requireAuthentication->execute();
        $identityPolicy = $accessPolicy->identityPolicy;

        if ($accessPolicy->requiredRole instanceof UserRole) {
            $this->requireRole->execute(requiredRole: $accessPolicy->requiredRole);
        }

        if ($accessPolicy->requiredPermission instanceof UserPermission) {
            $this->requirePermission->execute(permission: $accessPolicy->requiredPermission);
        }

        if ($accessPolicy->resourceOwnerUserId !== null) {
            $this->requireResourceOwner->execute(ownerUserId: $accessPolicy->resourceOwnerUserId);
        }

        if ($accessPolicy->phishingResistantRequired || $identityPolicy?->phishingResistantRequired === true) {
            $this->requirePhishingResistantAuthentication->execute();
        }

        $freshMfaMaxAgeSeconds = $accessPolicy->freshMfaMaxAgeSeconds
            ?? $identityPolicy?->freshMfaMaxAgeSeconds;

        if ($accessPolicy->freshMfa || $freshMfaMaxAgeSeconds !== null) {
            $this->requireFreshMfa->execute(maxAgeSeconds: $freshMfaMaxAgeSeconds);
        }

        if ($accessPolicy->adminElevation || $identityPolicy?->adminElevationRequired === true) {
            $this->requireAdminElevation->execute();
        }
    }
}
