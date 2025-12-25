<?php

declare(strict_types=1);

namespace Avax\Auth\Adapters;

use Avax\Auth\Contracts\PermissionInterface;
use Avax\Auth\Contracts\RoleInterface;
use Avax\Auth\Contracts\UserInterface;

/**
 * Service responsible for managing access control (Roles/Permissions).
 */
class AccessControl
{
    public function addRole(UserInterface $user, RoleInterface $role): bool
    {
        if (!$user->hasRole(role: $role->getRole())) {
            $user->addRole(role: $role);
            return true;
        }
        return false;
    }

    public function hasRole(UserInterface $user, string $role): bool
    {
        return $user->hasRole(role: $role);
    }

    public function removeRole(UserInterface $user, string $role): bool
    {
        if ($user->hasRole(role: $role)) {
            $user->removeRole(role: $role);
            return true;
        }
        return false;
    }

    public function addPermissionToRole(RoleInterface $role, PermissionInterface $permission): bool
    {
        if (!$role->hasPermission(permission: $permission->getPermission())) {
            $role->addPermission(permission: $permission);
            return true;
        }
        return false;
    }

    public function hasPermission(UserInterface $user, string $permission): bool
    {
        foreach ($user->getRoles() as $role) {
            if ($role->hasPermission($permission)) {
                return true;
            }
        }
        return false;
    }

    public function removePermissionFromRole(RoleInterface $role, PermissionInterface $permission): bool
    {
        if ($role->hasPermission(permission: $permission->getPermission())) {
            $role->removePermission(permission: $permission);
            return true;
        }
        return false;
    }

    public function check(UserInterface $user, string $policy): bool
    {
        if ($this->hasRole(user: $user, role: $policy)) {
            return true;
        }
        return $this->hasPermission(user: $user, permission: $policy);
    }
}
