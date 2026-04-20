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
    public bool      $isActive;
    /** @var list<UserPermission> */
    public array     $permissions;
    /** @var list<UserRole> */
    public array     $roles;
    public string    $passwordHash;
    public string    $username;
    public UserEmail $email;
    public UserId    $id;

    /**
     * @param array<UserRole>       $roles
     * @param array<UserPermission> $permissions
     */
    public function __construct(
        UserId                          $id,
        #[SensitiveParameter] UserEmail $email,
        string                          $username,
        #[SensitiveParameter] string    $passwordHash,
        array|null                      $roles = null,
        array|null                      $permissions = null,
        bool                            $isActive = true
    )
    {
        $roles              ??= [];
        $permissions        ??= [];
        $this->id           = $id;
        $this->email        = $email;
        $this->username     = $username;
        $this->passwordHash = $passwordHash;
        $this->roles        = $roles;
        $this->permissions  = $permissions;
        $this->isActive     = $isActive;
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
        if (in_array($role, $this->roles, true)) {
            return true;
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
