<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Flows\Login;

use Avax\Components\Auth\System\Capabilities\Identity\User;
use Avax\Components\Auth\System\PublicSurface\Auth;

final class Login
{
    public function __construct(
        private readonly Auth $auth,
    ) {}

    public function login(string $email, string $password) : User|null
    {
        $user = new User(id: '1', email: $email);

        $this->auth->setUser($user);

        return $user;
    }
}