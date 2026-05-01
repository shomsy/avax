<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities;

use Avax\Components\Identity\Access\System\Capabilities\Policy\AccessPolicy;
use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated;
use Avax\Components\Identity\Access\System\Capabilities\RequirePermission\PermissionDenied;
use Avax\Components\Identity\Access\System\Capabilities\RequireResourceOwner\ResourceOwnerDenied;
use Avax\Components\Identity\Access\System\Capabilities\RequireRole\RoleDenied;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserPermission;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserRole;
use Avax\Components\Identity\Credentials\System\Capabilities\Mfa\Runtime\Models\FreshMfaRequired;
use Avax\Components\Identity\Tenancy\System\Capabilities\AdminRealmRuntime\AdminElevationFailed;

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
