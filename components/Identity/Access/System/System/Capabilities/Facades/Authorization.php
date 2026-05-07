<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\System\Capabilities\Facades;

use Avax\Components\Identity\Access\System\System\Capabilities\AccessInterface;
use Avax\Components\Identity\Access\System\System\Capabilities\Policy\AccessPolicy;
use Avax\Components\Identity\Access\System\System\Capabilities\RequireAccessPolicy\RequireAccessPolicy as RequireAccessPolicyBoundary;
use Avax\Components\Identity\Access\System\System\Capabilities\RequireAuthentication\RequireAuthentication as RequireAuthenticationBoundary;
use Avax\Components\Identity\Access\System\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Access\System\System\Capabilities\RequirePermission\PermissionDenied;
use Avax\Components\Identity\Access\System\System\Capabilities\RequirePermission\RequirePermission as RequirePermissionBoundary;
use Avax\Components\Identity\Access\System\System\Capabilities\RequireResourceOwner\ResourceOwnerDenied;
use Avax\Components\Identity\Access\System\System\Capabilities\RequireRole\RequireRole as RequireRoleBoundary;
use Avax\Components\Identity\Access\System\System\Capabilities\RequireRole\RoleDenied;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserPermission;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserRole;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Models\FreshMfaRequired;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\AdminElevationFailed;
use SensitiveParameter;

/**
 * Root authorization logic for the access capability.
 */
final readonly class Authorization implements AccessInterface
{
    public function __construct(
        #[SensitiveParameter]
        private RequireAuthenticationBoundary $requireAuthenticationBoundary,
        private RequireRoleBoundary           $requireRoleBoundary,
        private RequirePermissionBoundary     $requirePermissionBoundary,
        #[SensitiveParameter]
        private RequireAccessPolicyBoundary   $requireAccessPolicyBoundary,
    ) {}

    /**
     * @throws Unauthenticated
     */
    public function requireAuthentication() : void
    {
        $this->requireAuthenticationBoundary->execute();
    }

    /**
     * @throws Unauthenticated
     * @throws RoleDenied
     */
    public function requireRole(UserRole $userRole) : void
    {
        $this->requireRoleBoundary->execute(requiredRole: $userRole);
    }

    /**
     * @throws Unauthenticated
     * @throws PermissionDenied
     */
    public function requirePermission(UserPermission $userPermission) : void
    {
        $this->requirePermissionBoundary->execute(permission: $userPermission);
    }

    /**
     * @throws AdminElevationFailed
     * @throws FreshMfaRequired
     * @throws PermissionDenied
     * @throws ResourceOwnerDenied
     * @throws RoleDenied
     * @throws Unauthenticated
     */
    public function requirePolicy(AccessPolicy $accessPolicy) : void
    {
        $this->requireAccessPolicyBoundary->execute(policy: $accessPolicy);
    }
}
