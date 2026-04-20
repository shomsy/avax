<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\User;

/**
 * Interface for user storage operations.
 */
interface UserStoreInterface
{
    public function findById(string $userId) : \Avax\Auth\System\Capabilities\User\User|null;

    public function findByEmail(string $email) : \Avax\Auth\System\Capabilities\User\User|null;

    public function save(\Avax\Auth\System\Capabilities\User\User $user) : void;
}