<?php

declare(strict_types=1);

namespace Avax\Auth;

use Avax\Auth\Actions\Check;
use Avax\Auth\Actions\GetUser;
use Avax\Auth\Actions\Login;
use Avax\Auth\Actions\Logout;
use Avax\Auth\Contracts\AuthInterface;
use Avax\Auth\Contracts\UserInterface;
use Avax\Auth\Data\Credentials;
use Avax\Auth\Exceptions\AuthFailed;
use SensitiveParameter;

final readonly class Authenticator implements AuthInterface
{
    public function __construct(
        private Login   $loginAction,
        private Logout  $logoutAction,
        private GetUser $getUserAction,
        private Check   $checkAction
    ) {}

    /**
     * @throws AuthFailed
     */
    public function login(#[SensitiveParameter] Credentials $credentials) : UserInterface
    {
        return $this->loginAction->execute(credentials: $credentials);
    }

    public function logout() : void
    {
        $this->logoutAction->execute();
    }

    public function user() : UserInterface|null
    {
        return $this->getUserAction->execute();
    }

    public function check() : bool
    {
        return $this->checkAction->execute();
    }
}
