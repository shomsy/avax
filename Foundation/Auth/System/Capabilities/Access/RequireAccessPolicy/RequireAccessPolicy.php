<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Access\RequireAccessPolicy;

use Avax\Auth\System\Capabilities\Access\Policy\AccessPolicy;
use Avax\Auth\System\Capabilities\Access\RequireAuthentication\RequireAuthentication;
use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\Access\RequirePermission\PermissionDenied;
use Avax\Auth\System\Capabilities\Access\RequirePermission\RequirePermission;
use Avax\Auth\System\Capabilities\Access\RequirePhishingResistantAuthentication\PhishingResistantAuthenticationRequired;
use Avax\Auth\System\Capabilities\Access\RequirePhishingResistantAuthentication\RequirePhishingResistantAuthentication;
use Avax\Auth\System\Capabilities\Access\RequireResourceOwner\RequireResourceOwner;
use Avax\Auth\System\Capabilities\Access\RequireResourceOwner\ResourceOwnerDenied;
use Avax\Auth\System\Capabilities\Access\RequireRole\RequireRole;
use Avax\Auth\System\Capabilities\Access\RequireRole\RoleDenied;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Models\FreshMfaRequired;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\StepUp\RequireFreshMfa;
use Avax\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\AdminElevationFailed;
use Avax\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\RequireAdminElevation\RequireAdminElevation;
use SensitiveParameter;

/**
 * Evaluates composed authorization requirements in one place.
 */
final readonly class RequireAccessPolicy
{
    private RequireAdminElevation                  $requireAdminElevation;
    private RequireFreshMfa                        $requireFreshMfa;
    private RequirePhishingResistantAuthentication $requirePhishingResistantAuthentication;
    private RequireResourceOwner                   $requireResourceOwner;
    private RequirePermission                      $requirePermission;
    private RequireRole                            $requireRole;
    private RequireAuthentication                  $requireAuthentication;

    public function __construct(
        #[SensitiveParameter] RequireAuthentication                  $requireAuthentication,
        RequireRole                                                  $requireRole,
        RequirePermission                                            $requirePermission,
        RequireResourceOwner                                         $requireResourceOwner,
        #[SensitiveParameter] RequirePhishingResistantAuthentication $requirePhishingResistantAuthentication,
        RequireFreshMfa                                              $requireFreshMfa,
        RequireAdminElevation                                        $requireAdminElevation
    )
    {
        $this->requireAuthentication                  = $requireAuthentication;
        $this->requireRole                            = $requireRole;
        $this->requirePermission                      = $requirePermission;
        $this->requireResourceOwner                   = $requireResourceOwner;
        $this->requirePhishingResistantAuthentication = $requirePhishingResistantAuthentication;
        $this->requireFreshMfa                        = $requireFreshMfa;
        $this->requireAdminElevation                  = $requireAdminElevation;
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
