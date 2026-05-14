<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\PublicSurface;

use Avax\Components\Identity\Auth\System\Flows\ChangePassword\ChangePasswordData;
use Avax\Components\Identity\Auth\System\Flows\Login\AuthenticationResult;
use Avax\Components\Identity\Auth\System\Flows\Login\Credentials;
use Avax\Components\Identity\Auth\System\Flows\Register\RegistrationData;
use Avax\Components\Identity\Auth\System\Flows\Register\RegistrationResult;

/**
 * AuthInterface - Enterprise-grade authentication and account management contract.
 */
interface AuthInterface
{
    public function login(Credentials $credentials) : AuthenticationResult;

    public function logout() : void;

    public function user() : User|null;

    public function check() : bool;

    public function guest() : bool;

    public function register(RegistrationData $registrationData) : RegistrationResult;

    public function changePassword(ChangePasswordData $changePasswordData) : void;

    public function logoutAllSessions() : void;
}
