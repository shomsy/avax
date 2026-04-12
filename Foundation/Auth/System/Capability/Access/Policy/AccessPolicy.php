<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Access\Policy;

use Avax\Auth\System\Capability\User\UserPermission;
use Avax\Auth\System\Capability\User\UserRole;

/**
 * Declarative authorization policy evaluated inside the access capability.
 */
final readonly class AccessPolicy
{
    public function __construct(
        public UserRole|null $requiredRole = null,
        public UserPermission|null $requiredPermission = null,
        public int|null $resourceOwnerUserId = null,
        public bool $freshMfa = false,
        public bool $adminElevation = false
    ) {}
}
