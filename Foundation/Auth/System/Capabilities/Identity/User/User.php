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
    public array $permissions;
    /** @var list<UserRole> */
    public array $roles;

    public function __construct(
        private UserId    $_id,
        private UserEmail $_email,
        private string    $_username,
        private string    $_passwordHash,
        array|null        $roles = null,
        array|null        $permissions = null,
        private bool      $_isActive = true
    )
    {
        $this->roles       = array_values(array: $roles ?? []);
        $this->permissions = array_values(array: $permissions ?? []);
    }

    public UserId $id {
        get => $this->_id;
    }

    public UserEmail $email {
        get => $this->_email;
    }

    public string $username {
        get => $this->_username;
    }

    public string $passwordHash {
        get => $this->_passwordHash;
    }

    public bool $isActive {
        get => $this->_isActive;
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
        return new self(
            _id          : $id,
            _email       : $email,
            _username    : $username,
            _passwordHash: $passwordHash,
            roles        : $roles,
            permissions  : $permissions,
            _isActive    : $isActive
        );
    }

    public function hasRole(UserRole $role) : bool
    {
        return Arrhae::of(items: $this->roles)->contains(needle: $role);
    }

    public function hasPermission(UserPermission $permission) : bool
    {
        return Arrhae::of(items: $this->permissions)
            ->any(callback: fn (UserPermission $p) => $p->equals(other: $permission));
    }

    public function canAccessRole(UserRole $requiredRole) : bool
    {
        return Arrhae::of(items: $this->roles)
            ->any(callback: fn (UserRole $role) => $role->canAccess(required: $requiredRole));
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
