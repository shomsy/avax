<?php

declare(strict_types=1);

namespace Avax\Auth\System;

use Avax\Auth\System\Flows\Login\Credentials;
use Avax\Auth\System\Flows\Register\RegistrationData;
use Avax\Auth\System\Flows\ChangePassword\ChangePasswordData;
use Avax\Auth\System\Capabilities\User\User;

/**
 * Interface AuthInterface
 *
 * Defines the contract for the core authentication system facade.
 */
interface AuthInterface
{
    public function login(Credentials $credentials) : User;
    public function logout() : void;
    public function check() : bool;
    public function user() : User|null;
    public function changePassword(User $user, ChangePasswordData $data) : void;
    public function register(RegistrationData $data) : User;
}
