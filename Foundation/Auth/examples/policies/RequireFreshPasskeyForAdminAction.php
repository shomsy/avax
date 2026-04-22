<?php

declare(strict_types=1);

namespace Avax\Auth\Examples\Policies;

use Avax\Auth\System\Capabilities\Access\Policy\AccessPolicy;
use Avax\Auth\System\Capabilities\Access\Policy\IdentityPolicyCatalog;
use Avax\Auth\System\Capabilities\Access\RequireAccessPolicy\RequireAccessPolicy;
use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\Access\RequirePermission\PermissionDenied;
use Avax\Auth\System\Capabilities\Access\RequirePhishingResistantAuthentication\PhishingResistantAuthenticationRequired;
use Avax\Auth\System\Capabilities\Access\RequireRole\RoleDenied;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Models\FreshMfaRequired;
use Avax\Auth\System\Capabilities\Identity\User\UserPermission;
use Avax\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\AdminElevationFailed;
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
     * @throws AdminElevationFailed
     * @throws FreshMfaRequired
     * @throws PhishingResistantAuthenticationRequired
     * @throws Unauthenticated
     * @throws PermissionDenied
     * @throws RoleDenied
     */
    public function execute() : void
    {
        $this->requireAccessPolicy->execute(policy: AccessPolicy::forIdentityPolicy(
            identityPolicy    : IdentityPolicyCatalog::admin(),
            requiredPermission: new UserPermission(value: 'admin.high_impact.write')
        ));
    }
}
