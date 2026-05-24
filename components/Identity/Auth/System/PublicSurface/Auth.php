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
final class Auth implements AuthInterface
{
    private static ?Auth $instance = null;

    /**
     * Set the global Auth instance (called by AuthServiceProvider during boot).
     * This enables the deprecated auth() helper without service locator.
     */
    public static function setInstance(Auth $auth) : void
    {
        self::$instance = $auth;
    }

    /**
     * Get the global Auth instance.
     *
     * @deprecated Inject AuthInterface through DI instead.
     */
    public static function instance() : Auth
    {
        if (self::$instance === null) {
            throw new \RuntimeException(
                'Auth instance not set. '
                . 'Ensure AuthServiceProvider is registered, or inject AuthInterface through DI.',
            );
        }

        return self::$instance;
    }

    /**
     * Reset static instance for long-lived worker safety.
     * MUST be called between requests in persistent runtimes.
     */
    public static function resetInstance() : void
    {
        self::$instance = null;
    }

    public function __construct(
        private Identity $identity,
    ) {}

    public function login(Credentials $credentials) : AuthenticationResult
    {
        return $this->identity->login($credentials);
    }

    public function logout() : void
    {
        $this->identity->logout();
    }

    public function user() : User|null
    {
        $entity = $this->identity->authentication()->user();

        return $entity ? User::fromEntity($entity) : null;
    }

    public function guest() : bool
    {
        return ! $this->check();
    }

    public function check() : bool
    {
        return $this->identity->authentication()->check();
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
