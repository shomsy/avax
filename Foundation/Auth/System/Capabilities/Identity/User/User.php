<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\User;

use SensitiveParameter;
use Stringable;

/**
 * User entity within the Avax Auth System.
 */
final readonly class User implements UserInterface, Stringable
{
    /** @var list<UserPermission> */
    public array     $permissions;
    /** @var list<UserRole> */
    public array     $roles;

    /**
     * @param array<UserRole>|null       $roles
     * @param array<UserPermission>|null $permissions
     */
    public function __construct(
        public UserId                          $id,
        #[SensitiveParameter] public UserEmail $email,
        public string                          $username,
        #[SensitiveParameter] public string    $passwordHash,
        array|null                      $roles = null,
        array|null                      $permissions = null,
        public bool                            $isActive = true
    )
    {
        $roles              ??= [];
        $permissions        ??= [];
        $this->roles = array_values(array: $roles);
        $this->permissions = array_values(array: $permissions);
    }

    /**
     * @param list<UserRole>|null       $roles
     * @param list<UserPermission>|null $permissions
     */
    public static function create(
        UserId                          $id,
        #[SensitiveParameter] UserEmail $email,
        string                          $username,
        #[SensitiveParameter] string    $passwordHash,
        array|null                      $roles = null,
        array|null                      $permissions = null,
        bool                            $isActive = true
    ) : self
    {
        $roles       ??= [];
        $permissions ??= [];

        return new self(id: $id, email: $email, username: $username, passwordHash: $passwordHash, roles: $roles, permissions: $permissions, isActive: $isActive);
    }

    public function hasRole(UserRole $role) : bool
    {
        if (in_array(needle: $role, haystack: $this->roles, strict: true)) {
            return true;
        }

        return false;
    }

    public function hasPermission(UserPermission $permission) : bool
    {
        return array_any(array: $this->permissions, callback: fn ($userPermission) => $userPermission->equals(other: $permission));
    }

    public function canAccessRole(UserRole $requiredRole) : bool
    {
        return array_any(array: $this->roles, callback: fn ($role) => $role->canAccess(required: $requiredRole));
    }

    public function isActive() : bool
    {
        return $this->isActive;
    }

    public function getId() : UserId
    {
        return $this->id;
    }

    public function getEmail() : UserEmail
    {
        return $this->email;
    }

    public function getUsername() : string
    {
        return $this->username;
    }

    public function getPasswordHash() : string
    {
        return $this->passwordHash;
    }

    /**
     * @return list<UserRole>
     */
    public function getRoles() : array
    {
        return $this->roles;
    }

    /**
     * @return list<UserPermission>
     */
    public function getPermissions() : array
    {
        return $this->permissions;
    }

    public function __toString() : string
    {
        return $this->email->value;
    }
}
