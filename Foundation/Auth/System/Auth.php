<?php

declare(strict_types=1);

namespace Avax\Auth\System;

use Avax\Auth\System\Capability\Access\AccessInterface;
use Avax\Auth\System\Flow\Login\Login;
use Avax\Auth\System\Flow\Login\Credentials;
use Avax\Auth\System\Flow\Logout\Logout;
use Avax\Auth\System\Flow\Register\Register;
use Avax\Auth\System\Flow\Register\RegistrationData;
use Avax\Auth\System\Flow\ChangePassword\ChangePassword;
use Avax\Auth\System\Flow\ChangePassword\ChangePasswordData;
use Avax\Auth\System\Flow\CheckAuthentication\CheckAuthentication;
use Avax\Auth\System\Flow\ReadCurrentUser\ReadCurrentUser;
use Avax\Auth\System\Configuration\AuthBuilder;
use Avax\Auth\System\Capability\User\User;
use Exception;
use SensitiveParameter;

/**
 * Main entry point for Avax Auth System.
 * 
 * Banal: The facade that connects all system flows.
 */
final readonly class Auth implements AuthInterface
{
    public function __construct(
        private Login                                     $login,
        private Logout                                    $logout,
        #[SensitiveParameter] private CheckAuthentication $checkAuthentication,
        private ReadCurrentUser                           $readCurrentUser,
        private AccessInterface                           $access,
        #[SensitiveParameter] private ChangePassword      $changePassword,
        private Register                                  $register
    ) {}

    /**
     * Start the fluent configuration builder.
     */
    public static function configuration() : AuthBuilder
    {
        return new AuthBuilder();
    }

    /**
     * @throws Exception
     */
    public function login(#[SensitiveParameter] Credentials $credentials) : User
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

    public function access() : AccessInterface
    {
        return $this->access;
    }

    /**
     * @throws Exception
     */
    public function changePassword(User $user, ChangePasswordData $data) : void
    {
        $this->changePassword->execute(user: $user, data: $data);
    }

    /**
     * @throws Exception
     */
    public function register(RegistrationData $data) : User
    {
        return $this->register->execute(data: $data);
    }
}
