<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\User;

use SensitiveParameter;
use Stringable;

/**
 * User entity within the Avax Auth System.
 */
final readonly class User implements UserInterface, Stringable
{
    /**
     * @param array<UserRole>       $roles
     * @param array<UserPermission> $permissions
     */
    public function __construct(
        public UserId                          $id,
        #[SensitiveParameter] public UserEmail $email,
        public string                          $username,
        #[SensitiveParameter] public string    $passwordHash,
        public array                           $roles = [],
        public array                           $permissions = [],
        public bool                            $isActive = true
    ) {}

    /**
     * @param list<UserRole>|null $roles
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
        foreach ($this->roles as $userRole) {
            if ($userRole === $role) {
                return true;
            }
        }

        return false;
    }

    public function hasPermission(UserPermission $permission) : bool
    {
        foreach ($this->permissions as $userPermission) {
            if ($userPermission->equals(other: $permission)) {
                return true;
            }
        }

        return false;
    }

    public function canAccessRole(UserRole $requiredRole) : bool
    {
        foreach ($this->roles as $role) {
            if ($role->canAccess(required: $requiredRole)) {
                return true;
            }
        }

        return false;
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
