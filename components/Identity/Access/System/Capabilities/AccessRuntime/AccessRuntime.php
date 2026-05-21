<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\AccessRuntime;

use Avax\Components\Identity\Access\System\Capabilities\Authorization\AuthorizationEngine;
use Avax\Components\Identity\Access\System\Flows\AdminElevation\BeginAdminElevation;
use Avax\Components\Identity\Access\System\Flows\AdminElevation\EndAdminElevation;
use Avax\Components\Identity\Access\System\Foundation\Exception\PermissionDenied;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserPermission;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserRole;

/**
 * AccessRuntime owns executable Access public-surface behavior.
 */
final readonly class AccessRuntime
{
    public function __construct(
        private AuthorizationEngine $authorizationEngine,
        private BeginAdminElevation $beginAdminElevation,
        private EndAdminElevation   $endAdminElevation,
    ) {}

    /**
     * @throws PermissionDenied
     */
    public function authorize(string $permission, mixed $resource = null) : void
    {
        if ($this->denies(permission: $permission, resource: $resource)) {
            throw new PermissionDenied('Permission denied: ' . $permission);
        }
    }

    public function denies(string $permission, mixed $resource = null) : bool
    {
        return ! $this->allows(permission: $permission, resource: $resource);
    }

    public function allows(string $permission, mixed $resource = null) : bool
    {
        if ($this->isElevated()) {
            return true;
        }

        return $this->authorizationEngine->check(permission: $permission, resource: $resource);
    }

    public function isElevated() : bool
    {
        return $this->beginAdminElevation->isActive();
    }

    public function beginElevation() : void
    {
        $this->beginAdminElevation->execute();
    }

    public function endElevation() : void
    {
        $this->endAdminElevation->execute();
    }

    public function requireAuthentication() : void
    {
    }

    public function requireRole(UserRole $userRole) : void
    {
    }

    public function requirePermission(UserPermission $userPermission) : void
    {
    }
}
