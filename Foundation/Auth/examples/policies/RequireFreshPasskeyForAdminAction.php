<?php

declare(strict_types=1);

namespace Avax\Auth\Examples\Policies;

use Avax\Auth\System\Capability\Access\Policy\AccessPolicy;
use Avax\Auth\System\Capability\Access\Policy\AssuranceTier;
use Avax\Auth\System\Capability\Access\Policy\AuthenticationFactor;
use Avax\Auth\System\Capability\Access\RequireAccessPolicy\RequireAccessPolicy;
use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\Access\RequirePermission\PermissionDenied;
use Avax\Auth\System\Capability\Access\RequireRole\RoleDenied;
use SensitiveParameter;

/**
 * Reference policy for high-impact admin actions that require recent passkey-backed assurance.
 */
final readonly class RequireFreshPasskeyForAdminAction
{
    private RequireAccessPolicy $requireAccessPolicy;

    public function __construct(
        #[SensitiveParameter] RequireAccessPolicy $requireAccessPolicy
    )
    {
        $this->requireAccessPolicy = $requireAccessPolicy;
    }

    /**
     * @throws Unauthenticated
     * @throws PermissionDenied
     * @throws RoleDenied
     */
    public function execute() : void
    {
        $this->requireAccessPolicy->execute(policy: new AccessPolicy(
                                                        requiredRoles                : ['admin'],
                                                        requiredPermissions          : ['admin.high_impact.write'],
                                                        phishingResistantRequired    : true,
                                                        requiredFreshMfa             : true,
                                                        freshMfaWithinSeconds        : 300,
                                                        minimumAssuranceTier         : AssuranceTier::VERY_HIGH,
                                                        acceptedAuthenticationFactors: [AuthenticationFactor::PASSKEY]
                                                    ));
    }
}
