<?php

declare(strict_types=1);

namespace Avax\Auth\Examples\Policies;

use Avax\Auth\System\Capability\Access\RequireAccessPolicy\RequireAccessPolicy;
use Avax\Auth\System\Capability\Access\Policy\AccessPolicy;
use Avax\Auth\System\Capability\Access\Policy\AssuranceTier;
use Avax\Auth\System\Capability\Access\Policy\AuthenticationFactor;

/**
 * Reference policy for high-impact admin actions that require recent passkey-backed assurance.
 */
final readonly class RequireFreshPasskeyForAdminAction
{
    public function __construct(
        private RequireAccessPolicy $requireAccessPolicy
    ) {}

    public function execute() : void
    {
        $this->requireAccessPolicy->execute(new AccessPolicy(
            requiredRoles               : ['admin'],
            requiredPermissions         : ['admin.high_impact.write'],
            phishingResistantRequired   : true,
            requiredFreshMfa            : true,
            freshMfaWithinSeconds       : 300,
            minimumAssuranceTier        : AssuranceTier::VERY_HIGH,
            acceptedAuthenticationFactors: [AuthenticationFactor::PASSKEY]
        ));
    }
}
