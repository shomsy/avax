<?php

declare(strict_types=1);

namespace Gemini\Auth\Application\Service\AccessControl;

use Gemini\Auth\Contracts\Identity\Subject\RABC\PermissionInterface;
use Gemini\Auth\Contracts\Identity\Subject\RABC\RoleInterface;
use Gemini\Auth\Contracts\Identity\Subject\UserInterface;

/**
 * Action class responsible for managing access control, specifically roles and permissions.
 */
class AccessControlService
{
    /**
     * Adds a role to a user if the user doesn't already have it.
     *
     * @param UserInterface $user The user to which the role will be added.
     * @param RoleInterface $role The role to add to the user.
     *
     * @return bool True if the role was added, false if the user already had the role.
     */
    public function addRole(UserInterface $user, RoleInterface $role) : bool
    {
        if (! $user->hasRole($role->getRole())) {
            $user->addRole($role);

            return true;
        }

        return false;
    }

    /**
     * Checks if a user has a specific role.
     *
     * @param UserInterface $user The user to check.
     * @param string        $role The role to check for.
     *
     * @return bool True if the user has the specified role, false otherwise.
     */
    public function hasRole(UserInterface $user, string $role) : bool
    {
        return $user->hasRole($role);
    }

    /**
     * Removes a role from a user if the user has it.
     *
     * @param UserInterface $user The user from which the role will be removed.
     * @param string        $role The role to remove from the user.
     *
     * @return bool True if the role was removed, false if the user didn't have the role.
     */
    public function removeRole(UserInterface $user, string $role) : bool
    {
        if ($user->hasRole($role)) {
            $user->removeRole($role);

            return true;
        }

        return false;
    }

    /**
     * Adds a permission to a role if the role doesn't already have it.
     *
     * @param RoleInterface       $role       The role to which the permission will be added.
     * @param PermissionInterface $permission The permission to add to the role.
     *
     * @return bool True if the permission was added, false if the role already had the permission.
     */
    public function addPermissionToRole(RoleInterface $role, PermissionInterface $permission) : bool
    {
        if (! $role->hasPermission($permission->getPermission())) {
            $role->addPermission($permission);

            return true;
        }

        return false;
    }

    /**
     * Checks if a user has a specific permission through any of their roles.
     *
     * @param UserInterface $user       The user to check.
     * @param string        $permission The permission to check for.
     *
     * @return bool True if the user has the permission, false otherwise.
     */
    public function hasPermission(UserInterface $user, string $permission) : bool
    {
        foreach ($user->getRoles() as $role) {
            if ($this->roleHasPermission($role, $permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * A helper function to check if a role has a specific permission.
     *
     * @param RoleInterface $role       The role to check.
     * @param string        $permission The permission to check for.
     *
     * @return bool True if the role has the permission, false otherwise.
     */
    private function roleHasPermission(RoleInterface $role, string $permission) : bool
    {
        return $role->hasPermission($permission);
    }

    /**
     * Removes a permission from a role if the role has it.
     *
     * @param RoleInterface       $role       The role from which the permission will be removed.
     * @param PermissionInterface $permission The permission to remove from the role.
     *
     * @return bool True if the permission was removed, false if the role didn't have the permission.
     */
    public function removePermissionFromRole(RoleInterface $role, PermissionInterface $permission) : bool
    {
        if ($role->hasPermission($permission->getPermission())) {
            $role->removePermission($permission);

            return true;
        }

        return false;
    }

    /**
     * Checks access to a given policy string (role or permission).
     *
     * @param UserInterface $user
     * @param string        $policy
     *
     * @return bool
     */
    public function check(UserInterface $user, string $policy) : bool
    {
        // First check role match
        if ($this->hasRole(user: $user, role: $policy)) {
            return true;
        }

        // Then fallback to permission match
        return $this->hasPermission(user: $user, permission: $policy);
    }

}
