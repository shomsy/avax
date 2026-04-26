<?php

declare(strict_types=1);

namespace components\Auth\System\Capabilities\Identity\User;

/**
 * Interface UserInterface within the Avax Auth System.
 */
interface UserInterface
{
    public function getId() : UserId;

    public function getEmail() : UserEmail;

    public function getUsername() : string;

    public function getPasswordHash() : string;

    /**
     * @return list<UserRole>
     */
    public function getRoles() : array;

    /**
     * @return list<UserPermission>
     */
    public function getPermissions() : array;

    public function hasRole(UserRole $role) : bool;

    public function hasPermission(UserPermission $permission) : bool;

    public function canAccessRole(UserRole $requiredRole) : bool;

    public function isActive() : bool;
}
