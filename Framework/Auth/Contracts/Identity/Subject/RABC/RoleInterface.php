<?php

declare(strict_types=1);

namespace Gemini\Auth\Contracts\Identity\Subject\RABC;

/**
 * Interface RoleInterface
 *
 * Defines the contract for role-based authentication, including permission management.
 */
interface RoleInterface
{
    public function getRole() : string;

    public function hasPermission(string $permission) : bool;

    /**
     * Add a permission to the role.
     */
    public function addPermission(PermissionInterface $permission) : void;

    /**
     * Remove a permission from the role.
     */
    public function removePermission(PermissionInterface $permission) : void;
}
