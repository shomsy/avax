<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Access;

use Avax\Auth\System\Capability\Access\RequireAuthentication\RequireAuthentication as RequireAuthenticationBoundary;
use Avax\Auth\System\Capability\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capability\Access\RequirePermission\PermissionDenied;
use Avax\Auth\System\Capability\Access\RequirePermission\RequirePermission as RequirePermissionBoundary;
use Avax\Auth\System\Capability\Access\RequireRole\RequireRole as RequireRoleBoundary;
use Avax\Auth\System\Capability\Access\RequireRole\RoleDenied;
use Avax\Auth\System\Capability\User\UserPermission;
use Avax\Auth\System\Capability\User\UserRole;

/**
 * Root access façade for the authorization capability.
 */
final readonly class Access implements AccessInterface
{
    public function __construct(
        private RequireAuthenticationBoundary $requireAuthentication,
        private RequireRoleBoundary           $requireRole,
        private RequirePermissionBoundary     $requirePermission
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
}
