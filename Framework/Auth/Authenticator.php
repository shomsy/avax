<?php

declare(strict_types=1);

namespace Gemini\Auth;

use Gemini\Auth\Actions\Check;
use Gemini\Auth\Actions\GetUser;
use Gemini\Auth\Actions\Login;
use Gemini\Auth\Actions\Logout;
use Gemini\Auth\Contracts\AuthInterface;
use Gemini\Auth\Contracts\UserInterface;
use Gemini\Auth\Data\Credentials;

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
