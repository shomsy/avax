<?php

declare(strict_types=1);

namespace Avax\Auth\Contracts;

/**
 * Interface UserInterface
 *
 * This interface defines a contract for user entities, specifying the essential methods required
 * for managing user data, roles, and permissions. By enforcing this contract, we ensure that any
 * class implementing `UserInterface` will provide consistent behavior in terms of these operations.
 */
interface UserInterface
{
    /**
     * Retrieves the unique identifier for the user.
     *
     * @return int The unique user identifier.
     */
    public function getId(): int;

    /**
     * Retrieves the user's email address.
     *
     * @return string The user's email.
     */
    public function getEmail(): string;

    /**
     * Retrieves the user's username.
     *
     * @return string The username.
     */
    public function getUsername(): string;

    /**
     * Retrieves the user's hashed password.
     *
     * @return string The hashed password.
     */
    public function getPassword(): string;

    /**
     * Sets the user's password.
     *
     * @param  string  $password  The new password to be set, which should be hashed.
     */
    public function setPassword(string $password): void;

    /**
     * Retrieves the roles associated with the user.
     *
     * @return array An array of roles associated with the user.
     */
    public function getRoles(): array;

    /**
     * Checks if the user has a specific permission.
     *
     * This method leverages the roles of the user to determine if the permission is granted.
     *
     * @param  string  $permission  The permission to check.
     * @return bool True if the user has the permission, false otherwise.
     */
    public function hasPermission(string $permission): bool;

    /**
     * Checks if the user has a specific role.
     *
     * @param  string  $role  The role to check for.
     * @return bool True if the user has the specified role, false otherwise.
     */
    public function hasRole(string $role): bool;

    /**
     * Adds a role to the user.
     *
     * This facilitates role-based access control by associating a new role with the user.
     *
     * @param  RoleInterface  $role  The role to add.
     */
    public function addRole(RoleInterface $role): void;

    /**
     * Removes a role from the user.
     *
     * This method is used to disassociate a given role from the user.
     *
     * @param  string  $role  The role identifier to remove.
     */
    public function removeRole(string $role): void;
}
