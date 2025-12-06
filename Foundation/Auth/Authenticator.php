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

final readonly class Authenticator implements AuthInterface
{
    public function __construct(
        private Login $loginAction,
        private Logout $logoutAction,
        private GetUser $getUserAction,
        private Check $checkAction
    ) {}

    public function login(Credentials $credentials): UserInterface
    {
        return $this->loginAction->execute($credentials);
    }

    public function logout(): void
    {
        $this->logoutAction->execute();
    }

    public function user(): ?UserInterface
    {
        return $this->getUserAction->execute();
    }

    public function check(): bool
    {
        return $this->checkAction->execute();
    }
}
