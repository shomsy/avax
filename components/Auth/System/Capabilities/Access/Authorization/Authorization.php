<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\Access\Authorization;

use components\Auth\System\Capabilities\Access\AccessInterface;
use components\Auth\System\Capabilities\Access\Policy\AccessPolicy;
use components\Auth\System\Capabilities\Access\RequireAccessPolicy\RequireAccessPolicy as RequireAccessPolicyBoundary;
use components\Auth\System\Capabilities\Access\RequireAuthentication\RequireAuthentication as RequireAuthenticationBoundary;
use components\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use components\Auth\System\Capabilities\Access\RequirePermission\PermissionDenied;
use components\Auth\System\Capabilities\Access\RequirePermission\RequirePermission as RequirePermissionBoundary;
use components\Auth\System\Capabilities\Access\RequireResourceOwner\ResourceOwnerDenied;
use components\Auth\System\Capabilities\Access\RequireRole\RequireRole as RequireRoleBoundary;
use components\Auth\System\Capabilities\Access\RequireRole\RoleDenied;
use components\Auth\System\Capabilities\Identity\Mfa\Runtime\Models\FreshMfaRequired;
use components\Auth\System\Capabilities\Identity\User\UserPermission;
use components\Auth\System\Capabilities\Identity\User\UserRole;
use components\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\AdminElevationFailed;
use SensitiveParameter;

/**
 * Root authorization logic for the access capability.
 */
final readonly class Authorization implements AccessInterface
{
    public function __construct(
        #[SensitiveParameter] private RequireAuthenticationBoundary $requireAuthentication,
        private RequireRoleBoundary                                 $requireRole,
        private RequirePermissionBoundary                           $requirePermission,
        #[SensitiveParameter] private RequireAccessPolicyBoundary   $requireAccessPolicy
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
