<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\PublicSurface;

use Avax\Components\Identity\Access\System\Capabilities\AccessRuntime\AccessRuntime;
use Avax\Components\Identity\Access\System\Capabilities\Policy\AccessPolicy;
use Avax\Components\Identity\Access\System\Foundation\Exception\PermissionDenied;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserPermission;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserRole;

/**
 * Access - Main entry point for Identity/Access component.
 */
final readonly class Access implements AccessInterface
{
    public function __construct(
        private AccessRuntime $runtime,
    ) {}

    /**
     * @throws PermissionDenied
     */
    public function authorize(string $permission, mixed $resource = null) : void
    {
        $this->runtime->authorize(permission: $permission, resource: $resource);
    }

    public function denies(string $permission, mixed $resource = null) : bool
    {
        return $this->runtime->denies(permission: $permission, resource: $resource);
    }

    public function allows(string $permission, mixed $resource = null) : bool
    {
        return $this->runtime->allows(permission: $permission, resource: $resource);
    }

    public function isElevated() : bool
    {
        return $this->runtime->isElevated();
    }

    public function beginElevation() : void
    {
        $this->runtime->beginElevation();
    }

    public function endElevation() : void
    {
        $this->runtime->endElevation();
    }

    public function requireAuthentication() : void
    {
        $this->runtime->requireAuthentication();
    }

    public function requireRole(UserRole $userRole) : void
    {
        $this->runtime->requireRole(userRole: $userRole);
    }

    public function requirePermission(UserPermission $userPermission) : void
    {
        $this->runtime->requirePermission(userPermission: $userPermission);
    }

    public function requirePolicy(AccessPolicy $accessPolicy) : void
    {
        $this->runtime->requirePolicy(accessPolicy: $accessPolicy);
    }

    public function requireResourceOwner(int $ownerUserId) : void
    {
        $this->runtime->requireResourceOwner(ownerUserId: $ownerUserId);
    }
}
