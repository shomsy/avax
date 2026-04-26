<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Access;

use Avax\Auth\System\Capabilities\Access\Policy\AccessPolicy;
use Avax\Auth\System\Capabilities\Access\RequireAuthentication\Unauthenticated;
use Avax\Auth\System\Capabilities\Access\RequirePermission\PermissionDenied;
use Avax\Auth\System\Capabilities\Access\RequireResourceOwner\ResourceOwnerDenied;
use Avax\Auth\System\Capabilities\Access\RequireRole\RoleDenied;
use Avax\Auth\System\Capabilities\Identity\Mfa\Runtime\Models\FreshMfaRequired;
use Avax\Auth\System\Capabilities\Identity\User\UserPermission;
use Avax\Auth\System\Capabilities\Identity\User\UserRole;
use Avax\Auth\System\Capabilities\Tenancy\AdminRealmRuntime\AdminElevationFailed;

/**
 * Unified access boundary contract for authorization enforcement.
 */
interface AccessInterface
{
    /**
     * @throws Unauthenticated
     */
    public function requireAuthentication() : void;

    /**
     * @throws RoleDenied
     * @throws Unauthenticated
     */
    public function requireRole(UserRole $requiredRole) : void;

    /**
     * @throws PermissionDenied
     * @throws Unauthenticated
     */
    public function requirePermission(UserPermission $permission) : void;

    /**
     * @throws AdminElevationFailed
     * @throws FreshMfaRequired
     * @throws PermissionDenied
     * @throws ResourceOwnerDenied
     * @throws RoleDenied
     * @throws Unauthenticated
     */
    public function requirePolicy(AccessPolicy $policy) : void;
}
