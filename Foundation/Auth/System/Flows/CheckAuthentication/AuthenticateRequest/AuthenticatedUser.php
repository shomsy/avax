<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\CheckAuthentication\AuthenticateRequest;

use Avax\Auth\System\Capabilities\Identity\User\User;
use Avax\Auth\System\Capabilities\Identity\User\UserPermission;
use Avax\Auth\System\Capabilities\Identity\User\UserRole;
use SensitiveParameter;

/**
 * Immutable public auth user snapshot.
 */
final readonly class AuthenticatedUser
{
    public bool   $emailVerified;
    /** @var list<string> */
    public array  $permissions;
    /** @var list<string> */
    public array  $roles;

    /**
     * @param list<string> $roles
     * @param list<string> $permissions
     */
    public function __construct(
        public int                          $id,
        #[SensitiveParameter] public string $email,
        public string                       $username,
        array|null                   $roles = null,
        array|null                   $permissions = null,
        bool|null                    $emailVerified = null,
        public bool                         $mfaEnabled = false
    )
    {
        $roles               ??= [];
        $permissions         ??= [];
        $emailVerified       ??= false;
        $this->roles         = $roles;
        $this->permissions   = $permissions;
        $this->emailVerified = $emailVerified;
    }

    public static function fromUser(
        User      $user,
        bool|null $emailVerified = null,
        bool      $mfaEnabled = false
    ) : self
    {
        $emailVerified ??= false;
        $roles         = array_map(
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
            roles        : $roles,
            permissions  : $permissions,
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
        return array_any($this->roles, fn ($storedRole) => UserRole::from(value: $storedRole)->canAccess(required: $requiredRole));
    }

    public function hasPermission(UserPermission $permission) : bool
    {
        return in_array($permission->value, $this->permissions, true);
    }
}
