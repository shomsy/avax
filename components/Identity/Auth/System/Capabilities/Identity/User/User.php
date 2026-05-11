<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\User;

use SensitiveParameter;
use Stringable;

/**
 * User entity within the Avax Auth System.
 */
final class User implements Stringable, UserInterface
{
    /** @var list<UserPermission> */
    public array $permissions;

    /** @var list<UserRole> */
    public array $roles;

    public function __construct(
        public UserId    $id,
        #[SensitiveParameter]
        public UserEmail $email,
        public string    $username,
        #[SensitiveParameter]
        public string $passwordHash, array|null $roles = null, array|null $permissions = null,
        public bool      $isActive = true,
    )
    {
        $this->roles       = array_values(array: $roles ?? []);
        $this->permissions = array_values(array: $permissions ?? []);
    }

    /**
     * @param list<UserRole>|null       $roles
     * @param list<UserPermission>|null $permissions
     */
    public static function create(
        UserId    $userId,
        #[SensitiveParameter]
        UserEmail $userEmail,
        string    $username,
        #[SensitiveParameter]
        string $passwordHash, array|null $roles = null, array|null $permissions = null,
        bool      $isActive = true,
    ) : self
    {
        return new self(
            roles        : $roles,
            permissions  : $permissions,
            _id          : $userId,
            _email       : $userEmail,
            _username    : $username,
            _passwordHash: $passwordHash,
            _isActive    : $isActive,
        );
    }

    public function hasRole(UserRole $userRole) : bool
    {
        return array_any($this->roles, fn ($existingRole) : bool => $existingRole === $userRole);
    }

    public function hasPermission(UserPermission $userPermission) : bool
    {
        return array_any($this->permissions, fn ($existingPermission) => $existingPermission->equals(other: $userPermission));
    }

    public function canAccessRole(UserRole $userRole) : bool
    {
        return array_any($this->roles, fn ($role) => $role->canAccess(required: $userRole));
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

    public function isActive() : bool
    {
        return $this->isActive;
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
