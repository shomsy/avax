<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\User;

/**
 * Interface for user storage operations.
 */
interface UserStoreInterface
{
    public function findById(string $userId) : \Avax\Auth\System\Capability\User\User|null;

    public function findByEmail(string $email) : \Avax\Auth\System\Capability\User\User|null;

    public function save(\Avax\Auth\System\Capability\User\User $user) : void;
}