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

    public UserId $id;

    public UserEmail $email;

    public string $username;

    public string $passwordHash;

    public bool $isActive;

    public function __construct(
        UserId $_id,
        #[SensitiveParameter]
        UserEmail $_email,
        string $_username,
        #[SensitiveParameter]
        string $_passwordHash,
        array $roles = null,
        array $permissions = null,
        bool $_isActive = true,
    ) {
        $this->id          = $_id;
        $this->email       = $_email;
        $this->username    = $_username;
        $this->passwordHash = $_passwordHash;
        $this->isActive    = $_isActive;
        $this->roles       = array_values(array: $roles ?? []);
        $this->permissions = array_values(array: $permissions ?? []);
    }

    /**
     * @param list<UserRole>|null       $roles
     * @param list<UserPermission>|null $permissions
     */
    public static function create(
        UserId $id,
        #[SensitiveParameter]
        UserEmail $email,
        string $username,
        #[SensitiveParameter]
        string $passwordHash,
        array $roles = null,
        array $permissions = null,
        bool $isActive = true,
    ): self {
        return new self(
            _id          : $id,
            _email       : $email,
            _username    : $username,
            _passwordHash: $passwordHash,
            roles        : $roles,
            permissions  : $permissions,
            _isActive    : $isActive,
        );
    }

    public function hasRole(UserRole $role): bool
    {
        foreach ($this->roles as $existingRole) {
            if ($existingRole === $role) {
                return true;
            }
        }

        return false;
    }

    public function hasPermission(UserPermission $permission): bool
    {
        foreach ($this->permissions as $existingPermission) {
            if ($existingPermission->equals(other: $permission)) {
                return true;
            }
        }

        return false;
    }

    public function canAccessRole(UserRole $requiredRole): bool
    {
        foreach ($this->roles as $role) {
            if ($role->canAccess(required: $requiredRole)) {
                return true;
            }
        }

        return false;
    }

    public function getId(): UserId
    {
        return $this->id;
    }

    public function getEmail(): UserEmail
    {
        return $this->email;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPasswordHash(): string
    {
        return $this->passwordHash;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @return list<UserRole>
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @return list<UserPermission>
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    public function __toString(): string
    {
        return $this->email->value;
    }
}
