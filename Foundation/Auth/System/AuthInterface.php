<?php

declare(strict_types=1);

namespace Avax\Auth\System;

use Avax\Auth\System\Capability\Access\AccessInterface;
use Avax\Auth\System\Capability\User\User;
use Avax\Auth\System\Flow\ChangePassword\ChangePasswordData;
use Avax\Auth\System\Flow\Login\Credentials;
use Avax\Auth\System\Flow\Register\RegistrationData;

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

    public function access() : AccessInterface;

    public function changePassword(User $user, ChangePasswordData $data) : void;

    public function register(RegistrationData $data) : User;
}
