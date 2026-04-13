<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\AuthenticateRequest;

use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserPermission;
use Avax\Auth\System\Capability\User\UserRole;

/**
 * Immutable public auth user snapshot.
 */
final readonly class AuthenticatedUser
{
    /**
     * @param list<string> $roles
     * @param list<string> $permissions
     */
    public function __construct(
        public int                           $id,
        #[\SensitiveParameter] public string $email,
        public string                        $username,
        public array                         $roles = [],
        public array                         $permissions = [],
        public bool                          $emailVerified = false,
        public bool                          $mfaEnabled = false
    ) {}

    public static function fromUser(
        User $user,
        bool $emailVerified = false,
        bool $mfaEnabled = false
    ) : self
    {
        $roles = array_map(
            static fn (UserRole $role) : string => $role->value,
            $user->getRoles()
        );

        $permissions = array_map(
            static fn (UserPermission $permission) : string => $permission->value,
            $user->getPermissions()
        );

        return new self(
            id           : $user->getId()->value,
            email        : $user->getEmail()->value,
            username     : $user->getUsername(),
            roles        : array_values($roles),
            permissions  : array_values($permissions),
            emailVerified: $emailVerified,
            mfaEnabled   : $mfaEnabled
        );
    }

    public function hasRole(UserRole $role) : bool
    {
        return in_array($role->value, $this->roles, true);
    }

    public function canAccessRole(UserRole $requiredRole) : bool
    {
        foreach ($this->roles as $storedRole) {
            if (UserRole::from(value: $storedRole)->canAccess(required: $requiredRole)) {
                return true;
            }
        }

        return false;
    }

    public function hasPermission(UserPermission $permission) : bool
    {
        return in_array($permission->value, $this->permissions, true);
    }
}
