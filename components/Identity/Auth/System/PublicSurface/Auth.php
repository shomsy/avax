<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\PublicSurface;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\Identity;
use Avax\Components\Identity\Auth\System\Flows\ChangePassword\ChangePasswordData;
use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Components\Identity\Auth\System\Flows\Login\Credentials;
use Avax\Components\Identity\Auth\System\Flows\Register\RegistrationData;
use Avax\Components\Identity\Auth\System\Flows\Register\RegistrationResult;

/**
 * Auth - Main entry point for Identity/Auth component.
 * Orchestrates flows and capabilities.
 */
final readonly class Auth implements AuthInterface
{
    public function __construct(
        private Identity $identity,
    ) {}

    public function login(Credentials $credentials): AuthenticationResult
    {
        return $this->identity->login($credentials);
    }

    public function logout(): void
    {
        $this->identity->logout();
    }

    public function user(): ?User
    {
        $entity = $this->identity->authentication()->user();

        return $entity ? User::fromEntity($entity) : null;
    }

    public function check(): bool
    {
        return $this->identity->authentication()->check();
    }

    public function guest(): bool
    {
        return ! $this->check();
    }

    public function register(RegistrationData $data): RegistrationResult
    {
        return $this->identity->account()->register($data);
    }

    public function changePassword(ChangePasswordData $data): void
    {
        $this->identity->account()->changePassword($data);
    }

    public function logoutAllSessions(): void
    {
        $this->identity->sessions()->logoutAllSessions();
    }
}
