<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\PublicSurface;

use Avax\Components\Identity\Auth\System\Capabilities\AuthenticationRuntime\AuthenticationRuntime;
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
        private AuthenticationRuntime $runtime,
    ) {}

    public function login(Credentials $credentials) : AuthenticationResult
    {
        return $this->runtime->login(credentials: $credentials);
    }

    public function logout() : void
    {
        $this->runtime->logout();
    }

    public function user() : User|null
    {
        return $this->runtime->user();
    }

    public function guest() : bool
    {
        return $this->runtime->guest();
    }

    public function check() : bool
    {
        return $this->runtime->check();
    }

    public function register(RegistrationData $registrationData) : RegistrationResult
    {
        return $this->runtime->register(registrationData: $registrationData);
    }

    public function changePassword(ChangePasswordData $changePasswordData) : void
    {
        $this->runtime->changePassword(changePasswordData: $changePasswordData);
    }

    public function logoutAllSessions() : void
    {
        $this->runtime->logoutAllSessions();
    }
}
