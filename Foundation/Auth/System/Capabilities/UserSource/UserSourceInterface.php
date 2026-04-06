<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\UserSource;

use Avax\Auth\System\Capabilities\User\User;
use Avax\Auth\System\Capabilities\User\UserId;
use Avax\Auth\System\Flows\Login\Credentials;

/**
 * Interface for user data operations within the Avax Auth System.
 */
interface UserSourceInterface
{
    public function findByCredentials(Credentials $credentials) : User|null;
    public function findById(UserId $id) : User|null;
    public function findByEmail(string $email) : User|null;
    public function create(User $user) : User;
    public function updatePassword(UserId $id, string $passwordHash) : void;
    public function emailExists(string $email) : bool;
    public function usernameExists(string $username) : bool;
}
