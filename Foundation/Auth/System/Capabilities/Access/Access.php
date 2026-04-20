<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Access;

use Avax\Auth\System\Capabilities\Access\Policy\AccessPolicy;
use Avax\Auth\System\Capabilities\Access\RequireAccessPolicy\RequireAccessPolicy as RequireAccessPolicyBoundary;
use Avax\Auth\System\Capabilities\Access\RequireAuthentication\RequireAuthentication as RequireAuthenticationBoundary;
use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\Access\RequirePermission\PermissionDenied;
use Avax\Auth\System\Capabilities\Access\RequirePermission\RequirePermission as RequirePermissionBoundary;
use Avax\Auth\System\Capabilities\Access\RequireRole\RequireRole as RequireRoleBoundary;
use Avax\Auth\System\Capabilities\Access\RequireRole\RoleDenied;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\FreshMfaRequired;
use Avax\Auth\System\Capabilities\Identity\User\UserPermission;
use Avax\Auth\System\Capabilities\Identity\User\UserRole;
use Avax\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\AdminElevationFailed;
use SensitiveParameter;

/**
 * Root access façade for the authorization capability.
 */
final readonly class Access implements AccessInterface
{
    private RequireAccessPolicyBoundary   $requireAccessPolicy;
    private RequirePermissionBoundary     $requirePermission;
    private RequireRoleBoundary           $requireRole;
    private RequireAuthenticationBoundary $requireAuthentication;

    public function __construct(
        #[SensitiveParameter] RequireAuthenticationBoundary $requireAuthentication,
        RequireRoleBoundary                                 $requireRole,
        RequirePermissionBoundary                           $requirePermission,
        #[SensitiveParameter] RequireAccessPolicyBoundary   $requireAccessPolicy
    )
    {
        $this->requireAuthentication = $requireAuthentication;
        $this->requireRole           = $requireRole;
        $this->requirePermission     = $requirePermission;
        $this->requireAccessPolicy   = $requireAccessPolicy;
    }

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
        $this->requireAccessPolicy->execute(policy: $policy);
    }
}
