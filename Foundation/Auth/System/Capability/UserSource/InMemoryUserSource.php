<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\UserSource;

use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Capability\User\UserId;
use Avax\Auth\System\Flow\Login\Credentials;
use SensitiveParameter;

/**
 * In-memory implementation of UserSource.
 * 
 * Capability: Useful for testing and prototyping.
 */
class InMemoryUserSource implements UserSourceInterface
{
    /** @var array<int, User> */
    private array $users = [];

    public function findById(UserId $id) : User|null
    {
        return $this->users[$id->value] ?? null;
    }

    public function findByCredentials(#[SensitiveParameter] Credentials $credentials) : User|null
    {
        $identifier = strtolower($credentials->identifier);

        foreach ($this->users as $user) {
            if (
                strtolower($user->getEmail()->value) === $identifier
                || strtolower($user->getUsername()) === $identifier
            ) {
                return $user;
            }
        }
        return null;
    }

    public function emailExists(#[SensitiveParameter] string $email) : bool
    {
        foreach ($this->users as $user) {
            if (strtolower($user->getEmail()->value) === strtolower($email)) {
                return true;
            }
        }
        return false;
    }

    public function findByEmail(#[SensitiveParameter] string $email) : User|null
    {
        foreach ($this->users as $user) {
            if (strtolower($user->getEmail()->value) === strtolower($email)) {
                return $user;
            }
        }
        return null;
    }

    public function usernameExists(string $username) : bool
    {
        foreach ($this->users as $user) {
            if (strtolower($user->getUsername()) === strtolower($username)) {
                return true;
            }
        }
        return false;
    }

    public function create(User $user) : User
    {
        $this->users[$user->getId()->value] = $user;
        return $user;
    }

    public function updatePassword(UserId $id, #[SensitiveParameter] string $passwordHash) : void
    {
        if (isset($this->users[$id->value])) {
            $user = $this->users[$id->value];
            
            // User is immutable, so we replace it with a new instance
            $this->users[$id->value] = User::create(
                id: $user->id,
                email: $user->email,
                username: $user->username,
                passwordHash: $passwordHash,
                roles: $user->roles,
                permissions: $user->permissions,
                isActive: $user->isActive
            );
        }
    }
}
