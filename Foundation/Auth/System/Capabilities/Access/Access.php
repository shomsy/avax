<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Access;

use Avax\Auth\System\Capabilities\Access\RequireAuthentication\RequireAuthentication as RequireAuthenticationBoundary;
use Avax\Auth\System\Capabilities\Access\RequirePermission\RequirePermission as RequirePermissionBoundary;
use Avax\Auth\System\Capabilities\Access\RequireRole\RequireRole as RequireRoleBoundary;
use Avax\Auth\System\Capabilities\User\UserPermission;
use Avax\Auth\System\Capabilities\User\UserRole;

/**
 * Root access façade for the authorization capability.
 */
final readonly class Access implements AccessInterface
{
    public function __construct(
        private RequireAuthenticationBoundary $requireAuthentication,
        private RequireRoleBoundary           $requireRole,
        private RequirePermissionBoundary      $requirePermission
    ) {}

    public function requireAuthentication() : void
    {
        $this->requireAuthentication->execute();
    }

    public function requireRole(UserRole $requiredRole) : void
    {
        $this->requireRole->execute(requiredRole: $requiredRole);
    }

    public function requirePermission(UserPermission $permission) : void
    {
        $this->requirePermission->execute(permission: $permission);
    }
}
