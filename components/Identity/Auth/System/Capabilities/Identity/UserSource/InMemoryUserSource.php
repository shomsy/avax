<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Flows\Login\Credentials;
use SensitiveParameter;

/**
 * In-memory implementation of UserSource.
 *
 * Capability: Useful for testing and prototyping.
 */
class InMemoryUserSource implements ProvisionableUserSourceInterface
{
    /** @var array<int, User> */
    private array $users = [];

    public function findById(UserId $id) : User|null
    {
        return $this->users[$id->value] ?? null;
    }

    public function findByCredentials(#[SensitiveParameter] Credentials $credentials) : User|null
    {
        $identifier = strtolower(string: $credentials->identifier);

        foreach ($this->users as $user) {
            if (
                strtolower(string: $user->getEmail()->value) === $identifier
                || strtolower(string: $user->getUsername()) === $identifier
            ) {
                return $user;
            }
        }

        return null;
    }

    public function emailExists(
        #[SensitiveParameter] string $email
    ) : bool
    {
        return array_any(array: $this->users, callback: static fn ($user) => strtolower(string: $user->getEmail()->value) === strtolower(string: $email));
    }

    public function findByEmail(#[SensitiveParameter] string $email) : User|null
    {
        foreach ($this->users as $user) {
            if (strtolower(string: $user->getEmail()->value) === strtolower(string: $email)) {
                return $user;
            }
        }

        return null;
    }

    public function usernameExists(string $username) : bool
    {
        return array_any(array: $this->users, callback: static fn ($user) => strtolower(string: $user->getUsername()) === strtolower(string: $username));
    }

    public function updatePassword(UserId $id, #[SensitiveParameter] string $passwordHash) : void
    {
        $this->replace(
            id    : $id,
            mutate: static fn (User $user) : User => User::create(
                id          : $user->id,
                email       : $user->email,
                username    : $user->username,
                passwordHash: $passwordHash,
                roles       : $user->roles,
                permissions : $user->permissions,
                isActive    : $user->isActive
            )
        );
    }

    /**
     * @param callable(User): User $mutate
     */
    private function replace(UserId $id, callable $mutate) : void
    {
        $user = $this->users[$id->value] ?? null;

        if ($user === null) {
            return;
        }

        $this->users[$id->value] = $mutate($user);
    }

    public function create(User $user) : User
    {
        $this->users[$user->getId()->value] = $user;

        return $user;
    }

    public function updateEmail(UserId $id, #[SensitiveParameter] string $email) : void
    {
        $this->replace(
            id    : $id,
            mutate: static fn (User $user) : User => User::create(
                id          : $user->id,
                email       : new UserEmail(value: $email),
                username    : $user->username,
                passwordHash: $user->passwordHash,
                roles       : $user->roles,
                permissions : $user->permissions,
                isActive    : $user->isActive
            )
        );
    }

    public function replaceRoles(UserId $id, array $roles) : void
    {
        $this->replace(
            id    : $id,
            mutate: static fn (User $user) : User => User::create(
                id          : $user->id,
                email       : $user->email,
                username    : $user->username,
                passwordHash: $user->passwordHash,
                roles       : $roles,
                permissions : $user->permissions,
                isActive    : $user->isActive
            )
        );
    }

    public function replacePermissions(UserId $id, array $permissions) : void
    {
        $this->replace(
            id    : $id,
            mutate: static fn (User $user) : User => User::create(
                id          : $user->id,
                email       : $user->email,
                username    : $user->username,
                passwordHash: $user->passwordHash,
                roles       : $user->roles,
                permissions : $permissions,
                isActive    : $user->isActive
            )
        );
    }

    public function deactivate(UserId $id) : void
    {
        $this->setActive(id: $id, isActive: false);
    }

    private function setActive(UserId $id, bool $isActive) : void
    {
        $this->replace(
            id    : $id,
            mutate: static fn (User $user) : User => User::create(
                id          : $user->id,
                email       : $user->email,
                username    : $user->username,
                passwordHash: $user->passwordHash,
                roles       : $user->roles,
                permissions : $user->permissions,
                isActive    : $isActive
            )
        );
    }

    public function activate(UserId $id) : void
    {
        $this->setActive(id: $id, isActive: true);
    }
}
