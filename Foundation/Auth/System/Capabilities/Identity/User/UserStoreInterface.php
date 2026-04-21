<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\User;

/**
 * Interface for user storage operations.
 */
interface UserStoreInterface
{
    public function findById(string $userId) : User|null;

    public function findByEmail(string $email) : User|null;

    public function save(User $user) : void;
}