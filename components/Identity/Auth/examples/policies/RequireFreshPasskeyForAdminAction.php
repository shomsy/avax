<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\Examples\Policies;

use Avax\Components\Identity\Access\System\Capabilities\Policy\AccessPolicy;
use Avax\Components\Identity\Access\System\Capabilities\Policy\IdentityPolicyCatalog;
use Avax\Components\Identity\Access\System\Capabilities\RequireAccessPolicy\RequireAccessPolicy;
use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Access\System\Capabilities\RequirePermission\PermissionDenied;
use Avax\Components\Identity\Access\System\Capabilities\RequirePhishingResistantAuthentication\PhishingResistantAuthenticationRequired;
use Avax\Components\Identity\Access\System\Capabilities\RequireRole\RoleDenied;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserPermission;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Models\FreshMfaRequired;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\AdminElevationFailed;
use SensitiveParameter;

/**
 * Reference policy for high-impact admin actions that require recent passkey-backed assurance.
 */
final readonly class RequireFreshPasskeyForAdminAction
{
    public function __construct(
        #[SensitiveParameter]
        private RequireAccessPolicy $requireAccessPolicy,
    ) {}

    /**
     * @throws AdminElevationFailed
     * @throws FreshMfaRequired
     * @throws PhishingResistantAuthenticationRequired
     * @throws Unauthenticated
     * @throws PermissionDenied
     * @throws RoleDenied
     */
    public function execute(): void
    {
        $this->requireAccessPolicy->execute(policy: AccessPolicy::forIdentityPolicy(
            identityPolicy    : IdentityPolicyCatalog::admin(),
            requiredPermission: new UserPermission(value: 'admin.high_impact.write'),
        ));
    }
}
