<?php

declare(strict_types=1);

namespace Avax\Auth\Examples\Policies;

use Avax\Auth\System\Capabilities\Access\Policy\AccessPolicy;
use Avax\Auth\System\Capabilities\Access\Policy\AssuranceTier;
use Avax\Auth\System\Capabilities\Access\Policy\AuthenticationFactor;
use Avax\Auth\System\Capabilities\Access\RequireAccessPolicy\RequireAccessPolicy;
use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\Access\RequirePermission\PermissionDenied;
use Avax\Auth\System\Capabilities\Access\RequireRole\RoleDenied;
use SensitiveParameter;

/**
 * Reference policy for high-impact admin actions that require recent passkey-backed assurance.
 */
final readonly class RequireFreshPasskeyForAdminAction
{
    public function __construct(
        #[SensitiveParameter] private RequireAccessPolicy $requireAccessPolicy
    )
    {
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
