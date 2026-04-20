<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Access;

use Avax\Auth\System\Capabilities\Access\Policy\AccessPolicy;
use Avax\Auth\System\Capabilities\User\UserPermission;
use Avax\Auth\System\Capabilities\User\UserRole;

/**
 * Unified access boundary contract for authorization enforcement.
 */
interface AccessInterface
{
    public function requireAuthentication() : void;

    public function requireRole(UserRole $requiredRole) : void;

    public function requirePermission(UserPermission $permission) : void;

    public function requirePolicy(AccessPolicy $policy) : void;
}
