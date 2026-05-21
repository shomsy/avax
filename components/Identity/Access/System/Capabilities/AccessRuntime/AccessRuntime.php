<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Access\System\Capabilities\AccessRuntime;

use Avax\Components\Identity\Access\System\Capabilities\Authorization\AuthorizationEngine;
use Avax\Components\Identity\Access\System\Capabilities\Policy\AccessPolicy;
use Avax\Components\Identity\Access\System\Capabilities\RequireAccessPolicy\RequireAccessPolicy;
use Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\RequireAuthentication;
use Avax\Components\Identity\Access\System\Capabilities\RequirePermission\RequirePermission;
use Avax\Components\Identity\Access\System\Capabilities\RequireResourceOwner\RequireResourceOwner;
use Avax\Components\Identity\Access\System\Capabilities\RequireRole\RequireRole;
use Avax\Components\Identity\Access\System\Flows\AdminElevation\BeginAdminElevation;
use Avax\Components\Identity\Access\System\Flows\AdminElevation\EndAdminElevation;
use Avax\Components\Identity\Access\System\Foundation\Exception\PermissionDenied;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserPermission;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserRole;
use SensitiveParameter;

/**
 * AccessRuntime — fluent enforcement of access control boundaries.
 *
 * Reads like a fluent API unit:
 *   $this->authentication->requireUser()
 *   $this->roles->require($role)
 *   $this->permissions->require($permission)
 *   $this->authorization->allows($permission, $resource)
 *   $this->policies->require($policy)
 *   $this->ownership->requireOwner($userId)
 *   $this->elevation->begin()
 */
final readonly class AccessRuntime
{
    public function __construct(
        #[SensitiveParameter]
        private RequireAuthentication  $authentication,
        private RequireRole            $roles,
        private RequirePermission      $permissions,
        private AuthorizationEngine    $authorization,
        private RequireResourceOwner   $ownership,
        private RequireAccessPolicy    $policies,
        private BeginAdminElevation    $elevation,
        private EndAdminElevation      $endElevation,
    ) {}

    /**
     * @throws PermissionDenied
     */
    public function authorize(string $permission, mixed $resource = null) : void
    {
        if ($this->denies(permission: $permission, resource: $resource)) {
            throw new PermissionDenied(message: 'Permission denied: ' . $permission);
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

        return $this->authorization->check(permission: $permission, resource: $resource);
    }

    public function isElevated() : bool
    {
        return $this->elevation->isActive();
    }

    public function beginElevation() : void
    {
        $this->elevation->execute();
    }

    public function endElevation() : void
    {
        $this->endElevation->execute();
    }

    /**
     * @throws \Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated
     */
    public function requireAuthentication() : void
    {
        $this->authentication->execute();
    }

    /**
     * @throws \Avax\Components\Identity\Access\System\Capabilities\RequireRole\RoleDenied
     * @throws \Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated
     */
    public function requireRole(UserRole $userRole) : void
    {
        $this->roles->execute(userRole: $userRole);
    }

    /**
     * @throws PermissionDenied
     * @throws \Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated
     */
    public function requirePermission(UserPermission $userPermission) : void
    {
        $this->permissions->execute(userPermission: $userPermission);
    }

    /**
     * @throws PermissionDenied
     * @throws \Avax\Components\Identity\Access\System\Capabilities\RequireRole\RoleDenied
     * @throws \Avax\Components\Identity\Access\System\Capabilities\RequireResourceOwner\ResourceOwnerDenied
     * @throws \Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated
     */
    public function requirePolicy(AccessPolicy $accessPolicy) : void
    {
        $this->policies->execute(accessPolicy: $accessPolicy);
    }

    /**
     * @throws \Avax\Components\Identity\Access\System\Capabilities\RequireResourceOwner\ResourceOwnerDenied
     * @throws \Avax\Components\Identity\Access\System\Capabilities\RequireAuthentication\Unauthenticated
     */
    public function requireResourceOwner(int $ownerUserId) : void
    {
        $this->ownership->execute(ownerUserId: $ownerUserId);
    }
}
