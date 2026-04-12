<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Access;

use Avax\Auth\System\Capability\Access\Policy\AccessPolicy;
use Avax\Auth\System\Capability\Access\RequireAuthentication\RequireAuthentication as RequireAuthenticationBoundary;
use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\Access\RequireAccessPolicy\RequireAccessPolicy as RequireAccessPolicyBoundary;
use Avax\Auth\System\Capability\Access\RequirePermission\PermissionDenied;
use Avax\Auth\System\Capability\Access\RequirePermission\RequirePermission as RequirePermissionBoundary;
use Avax\Auth\System\Capability\Access\RequireRole\RequireRole as RequireRoleBoundary;
use Avax\Auth\System\Capability\Access\RequireRole\RoleDenied;
use Avax\Auth\System\Capability\User\UserPermission;
use Avax\Auth\System\Capability\User\UserRole;
use Avax\Auth\System\Flow\AdminRealm\AdminElevationFailed;
use Avax\Auth\System\Flow\Mfa\FreshMfaRequired;

/**
 * Root access façade for the authorization capability.
 */
final readonly class Access implements AccessInterface
{
    public function __construct(
        private RequireAuthenticationBoundary $requireAuthentication,
        private RequireRoleBoundary           $requireRole,
        private RequirePermissionBoundary     $requirePermission,
        private RequireAccessPolicyBoundary   $requireAccessPolicy
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
     * @throws RoleDenied
     * @throws Unauthenticated
     */
    public function requirePolicy(AccessPolicy $policy) : void
    {
        $this->requireAccessPolicy->execute($policy);
    }
}
