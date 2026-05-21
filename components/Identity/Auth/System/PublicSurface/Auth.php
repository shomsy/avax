<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\PublicSurface;

use Avax\Components\Identity\Auth\System\Capabilities\Identity\Identity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\User as UserEntity;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserEmail;
use Avax\Components\Identity\Auth\System\Capabilities\Identity\User\UserId;
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

    public function login(Credentials $credentials) : AuthenticationResult
    {
        return $this->identity->login($credentials);
    }

    public function logout() : void
    {
        $this->identity->sessionIdentity()?->clear();
    }

    public function user() : User|null
    {
        $userId = $this->identity->sessionIdentity()?->resolveUserId();

        if ($userId === null) {
            return null;
        }

        return User::fromEntity(
            new UserEntity(
                id          : new UserId(value: $userId),
                email       : new UserEmail(value: ''),
                username    : '',
                passwordHash: '',
            ),
        );
    }

    public function guest() : bool
    {
        return ! $this->check();
    }

    public function check() : bool
    {
        return $this->identity->sessionIdentity()?->resolveUserId() !== null;
    }

    public function register(RegistrationData $registrationData) : RegistrationResult
    {
        return $this->identity->account()->register($registrationData);
    }

    public function changePassword(ChangePasswordData $changePasswordData) : void
    {
        $this->identity->account()->changePassword($changePasswordData);
    }

    public function logoutAllSessions() : void
    {
        $this->identity->sessions()->logoutAllSessions();
    }
}
