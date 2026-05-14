<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\UserSource;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
use Avax\Components\Identity\Auth\System\Flows\Login\Credentials;

/**
 * Interface for user data operations within the Avax Auth System.
 */
interface UserSourceInterface
{
    public function findByCredentials(Credentials $credentials) : User|null;

    public function findById(UserId $userId) : User|null;

    public function findByEmail(string $email) : User|null;

    public function create(User $user) : User;

    public function updatePassword(UserId $userId, string $passwordHash) : void;

    public function emailExists(string $email) : bool;

    public function usernameExists(string $username) : bool;
}
