<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Access;

use Avax\Auth\System\Capability\User\UserPermission;
use Avax\Auth\System\Capability\User\UserRole;

/**
 * Unified access boundary contract for authorization enforcement.
 */
interface AccessInterface
{
    public function requireAuthentication() : void;

    public function requireRole(UserRole $requiredRole) : void;

    public function requirePermission(UserPermission $permission) : void;
}
