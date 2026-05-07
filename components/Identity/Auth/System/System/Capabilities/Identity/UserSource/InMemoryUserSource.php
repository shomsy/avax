<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\System\Capabilities\Identity\UserSource;

use Avax\Components\Identity\Auth\System\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\System\Capabilities\Identity\User\UserEmail;
use Avax\Components\Identity\Auth\System\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\System\Flows\Login\Credentials;
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

    public function findById(UserId $userId) : ?User
    {
        return $this->users[$userId->value] ?? null;
    }

    public function findByCredentials(#[SensitiveParameter] Credentials $credentials) : ?User
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
        #[SensitiveParameter]
        string $email,
    ) : bool
    {
        return array_any(array: $this->users, callback: static fn ($user) : bool => strtolower(string: (string) $user->getEmail()->value) === strtolower(string: $email));
    }

    public function findByEmail(#[SensitiveParameter] string $email) : ?User
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
        return array_any(array: $this->users, callback: static fn ($user) : bool => strtolower(string: (string) $user->getUsername()) === strtolower(string: $username));
    }

    public function updatePassword(UserId $userId, #[SensitiveParameter] string $passwordHash) : void
    {
        $this->replace(
            mutate: static fn (User $user) : User => User::create(
                username    : $user->username,
                passwordHash: $passwordHash,
                roles       : $user->roles,
                permissions : $user->permissions,
                isActive    : $user->isActive,
                id          : $user->id,
                email       : $user->email,
            ),
            id    : $userId,
        );
    }

    /**
     * @param callable(User) : User $mutate
     */
    private function replace(UserId $userId, callable $mutate) : void
    {
        $user = $this->users[$userId->value] ?? null;

        if ($user === null) {
            return;
        }

        $this->users[$userId->value] = $mutate($user);
    }

    public function create(User $user) : User
    {
        $this->users[$user->getId()->value] = $user;

        return $user;
    }

    public function updateEmail(UserId $userId, #[SensitiveParameter] string $email) : void
    {
        $this->replace(
            mutate: static fn (User $user) : User => User::create(
                username    : $user->username,
                passwordHash: $user->passwordHash,
                roles       : $user->roles,
                permissions : $user->permissions,
                isActive    : $user->isActive,
                id          : $user->id,
                email       : new UserEmail(value: $email),
            ),
            id    : $userId,
        );
    }

    public function replaceRoles(UserId $userId, array $roles) : void
    {
        $this->replace(
            mutate: static fn (User $user) : User => User::create(
                username    : $user->username,
                passwordHash: $user->passwordHash,
                roles       : $roles,
                permissions : $user->permissions,
                isActive    : $user->isActive,
                id          : $user->id,
                email       : $user->email,
            ),
            id    : $userId,
        );
    }

    public function replacePermissions(UserId $userId, array $permissions) : void
    {
        $this->replace(
            mutate: static fn (User $user) : User => User::create(
                username    : $user->username,
                passwordHash: $user->passwordHash,
                roles       : $user->roles,
                permissions : $permissions,
                isActive    : $user->isActive,
                id          : $user->id,
                email       : $user->email,
            ),
            id    : $userId,
        );
    }

    public function deactivate(UserId $userId) : void
    {
        $this->setActive(isActive: false, id: $userId);
    }

    private function setActive(UserId $userId, bool $isActive) : void
    {
        $this->replace(
            mutate: static fn (User $user) : User => User::create(
                username    : $user->username,
                passwordHash: $user->passwordHash,
                roles       : $user->roles,
                permissions : $user->permissions,
                isActive    : $isActive,
                id          : $user->id,
                email       : $user->email,
            ),
            id    : $userId,
        );
    }

    public function activate(UserId $userId) : void
    {
        $this->setActive(isActive: true, id: $userId);
    }
}
