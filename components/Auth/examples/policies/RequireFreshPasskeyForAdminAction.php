<?php

declare(strict_types=1);

namespace components\Auth\Examples\Policies;

use components\Auth\System\Capabilities\Access\Policy\AccessPolicy;
use components\Auth\System\Capabilities\Access\Policy\IdentityPolicyCatalog;
use components\Auth\System\Capabilities\Access\RequireAccessPolicy\RequireAccessPolicy;
use components\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use components\Auth\System\Capabilities\Access\RequirePermission\PermissionDenied;
use components\Auth\System\Capabilities\Access\RequirePhishingResistantAuthentication\PhishingResistantAuthenticationRequired;
use components\Auth\System\Capabilities\Access\RequireRole\RoleDenied;
use components\Auth\System\Capabilities\Identity\Mfa\Runtime\Models\FreshMfaRequired;
use components\Auth\System\Capabilities\Identity\User\UserPermission;
use components\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\AdminElevationFailed;
use SensitiveParameter;

/**
 * Reference policy for high-impact admin actions that require recent passkey-backed assurance.
 */
final readonly class RequireFreshPasskeyForAdminAction
{
    public function __construct(
        #[SensitiveParameter] private RequireAccessPolicy $requireAccessPolicy
    ) {}

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
