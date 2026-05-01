<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\Facades;

use Avax\Components\Identity\Access\System\Capabilities\AccessInterface;
use Avax\Components\Identity\Access\System\Capabilities\Policy\AccessPolicy;
use Avax\Components\Identity\Access\System\Capabilities\RequireAccessPolicy\RequireAccessPolicy as RequireAccessPolicyBoundary;
use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\RequireAuthentication as RequireAuthenticationBoundary;
use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Access\System\Capabilities\RequirePermission\PermissionDenied;
use Avax\Components\Identity\Access\System\Capabilities\RequirePermission\RequirePermission as RequirePermissionBoundary;
use Avax\Components\Identity\Access\System\Capabilities\RequireResourceOwner\ResourceOwnerDenied;
use Avax\Components\Identity\Access\System\Capabilities\RequireRole\RequireRole as RequireRoleBoundary;
use Avax\Components\Identity\Access\System\Capabilities\RequireRole\RoleDenied;
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
        private RequireAuthenticationBoundary $requireAuthentication,
        private RequireRoleBoundary           $requireRole,
        private RequirePermissionBoundary     $requirePermission,
        #[SensitiveParameter]
        private RequireAccessPolicyBoundary   $requireAccessPolicy,
    ) {}

    /**
     * @throws Unauthenticated
     */
    public function requireAuthentication() : void
    {
        $this->requireAuthentication->execute();
    }

    /**
     * @throws Unauthenticated
     * @throws RoleDenied
     */
    public function requireRole(UserRole $requiredRole) : void
    {
        $this->requireRole->execute(requiredRole: $requiredRole);
    }

    /**
     * @throws Unauthenticated
     * @throws PermissionDenied
     */
    public function requirePermission(UserPermission $permission) : void
    {
        $this->requirePermission->execute(permission: $permission);
    }

    /**
     * @throws AdminElevationFailed
     * @throws FreshMfaRequired
     * @throws PermissionDenied
     * @throws ResourceOwnerDenied
     * @throws RoleDenied
     * @throws Unauthenticated
     */
    public function requirePolicy(AccessPolicy $policy) : void
    {
        $this->requireAccessPolicy->execute(policy: $policy);
    }
}
