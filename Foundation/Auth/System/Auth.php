<?php

declare(strict_types=1);

namespace Avax\Auth\System;

use Avax\Auth\System\Flows\Login\Login;
use Avax\Auth\System\Flows\Login\Credentials;
use Avax\Auth\System\Flows\Logout\Logout;
use Avax\Auth\System\Flows\Register\Register;
use Avax\Auth\System\Flows\Register\RegistrationData;
use Avax\Auth\System\Flows\ChangePassword\ChangePassword;
use Avax\Auth\System\Flows\ChangePassword\ChangePasswordData;
use Avax\Auth\System\Flows\CheckAuthentication\CheckAuthentication;
use Avax\Auth\System\Flows\ReadCurrentUser\ReadCurrentUser;
use Avax\Auth\System\Configuration\AuthBuilder;
use Avax\Auth\System\Capabilities\User\User;

/**
 * Main entry point for Avax Auth System.
 * 
 * Banal: The facade that connects all system flows.
 */
final readonly class Auth implements AuthInterface
{
    public function __construct(
        private Login                                      $login,
        private Logout                                     $logout,
        #[\SensitiveParameter] private CheckAuthentication $checkAuthentication,
        private ReadCurrentUser                            $readCurrentUser,
        #[\SensitiveParameter] private ChangePassword      $changePassword,
        private Register                                   $register
    ) {}

    /**
     * Start the fluent configuration builder.
     */
    public static function configuration() : AuthBuilder
    {
        return new AuthBuilder();
    }

    /**
     * @throws \Exception
     */
    public function login(#[\SensitiveParameter] Credentials $credentials) : User
    {
        return $this->login->execute(credentials: $credentials);
    }

    public function logout() : void
    {
        $this->logout->execute();
    }

    public function check() : bool
    {
        return $this->checkAuthentication->execute();
    }

    public function user() : User|null
    {
        return $this->readCurrentUser->execute();
    }

    /**
     * @throws \Exception
     */
    public function changePassword(User $user, ChangePasswordData $data) : void
    {
        $this->changePassword->execute(user: $user, data: $data);
    }

    /**
     * @throws \Exception
     */
    public function register(RegistrationData $data) : User
    {
        return $this->register->execute(data: $data);
    }
}
