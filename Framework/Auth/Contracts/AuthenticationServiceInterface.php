<?php

declare(strict_types=1);

namespace Gemini\Auth\Contracts;

use Gemini\Auth\Contracts\Identity\Subject\UserInterface;

/**
 * Interface AuthenticationServiceInterface
 *
 * Defines the contract for authentication-related operations.
 * This interface standardizes methods for user login and logout.
 */
interface AuthenticationServiceInterface
{
    /**
     * Authenticate and log in a user with the given credentials.
     *
     * @param \Gemini\Auth\Contracts\CredentialsInterface $credentials AccessControl credentials, usually an email and
     *                                                                 password.
     *
     * @return UserInterface The authenticated user instance.
     */
    public function login(CredentialsInterface $credentials) : UserInterface;

    /**
     * Log out the currently authenticated user.
     *
     * This method might clear user session, tokens, or other forms of persistent authentication.
     */
    public function logout() : void;
}
