<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity;

use Avax\Auth\System\Capabilities\User\User;

/**
 * Unified authentication identity contract for the Auth System.
 */
interface IdentityInterface
{
    public function issue(User $user) : string|null;

    public function authenticate(#[\SensitiveParameter] string $token) : void;

    public function clear() : void;

    public function check() : bool;

    public function token() : string|null;

    public function getCurrentUser() : User|null;

    public function getUserId() : int|null;
}
