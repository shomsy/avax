<?php

declare(strict_types=1);

namespace Gemini\Auth\Contracts;

use Gemini\Auth\Contracts\CredentialsInterface;
use Gemini\Auth\Contracts\Identity\Subject\UserInterface;

/**
 * IdentityInterface defines the basic contract for authentication mechanisms.
 * This interface is implemented by classes responsible for handling authentication operations.
 */
interface IdentityInterface
{
    /**
     * Attempt to authenticate a user with the provided credentials.
     * This method returns true if authentication is successful, otherwise false.
     *
     * @param CredentialsInterface $credentials The user's credentials (e.g., email and password).
     *
     * @return bool True on successful authentication, false otherwise.
     */
    public function attempt(CredentialsInterface $credentials) : bool;

    /**
     * Retrieve the currently authenticated user.
     * Returns null if no user is authenticated.
     *
     * @return UserInterface|null The authenticated user, or null if no user is authenticated.
     */
    public function user() : UserInterface|null;

    /**
     * Log out the currently authenticated user.
     * This should invalidate the current session or token.
     */
    public function logout() : void;

    /**
     * Check if a user is currently authenticated.
     *
     * @return bool True if a user is authenticated, otherwise false.
     */
    public function check() : bool;
}
