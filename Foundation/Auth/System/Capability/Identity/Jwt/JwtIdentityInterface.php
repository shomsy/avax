<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capability\Identity\Jwt;

use Avax\Auth\System\Capability\User\User;

/**
 * Interface JwtIdentityInterface within the Auth System.
 */
interface JwtIdentityInterface
{
    public function issue(User $user) : string;

    public function validate(string $token) : User|null;

    public function token() : string|null;

    public function getCurrentUser() : User|null;

    public function authenticate(string $token) : void;

    public function clear() : void;

    public function check() : bool;
}
