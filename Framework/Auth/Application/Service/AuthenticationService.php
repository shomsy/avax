<?php

declare(strict_types=1);

namespace Gemini\Auth\Application\Service;

use Gemini\Auth\Contracts\AuthenticationServiceInterface;
use Gemini\Auth\Contracts\CredentialsInterface;
use Gemini\Auth\Contracts\Identity\IdentityInterface;
use Gemini\Auth\Contracts\Identity\Subject\UserInterface;
use Gemini\Auth\Domain\Exception\AuthenticationException;

/**
 * Class AuthenticationService
 *
 * Provides core authentication logic using dependency injection for guard and session management.
 */
final readonly class AuthenticationService implements AuthenticationServiceInterface
{
    /**
     * AuthenticationService constructor.
     *
     * @param IdentityInterface $identity The guard responsible for session authentication.
     *
     * @technical This constructor sets up the service with a guard instance responsible for handling
     *            authentication-related tasks such as verifying user credentials, managing sessions, and user
     *            retrieval.
     * @business  Initializes the service to manage user authentication seamlessly, enabling secure login/logout
     *            functionality.
     */
    public function __construct(private IdentityInterface $identity) {}

    /**
     * Attempts to authenticate a user with given credentials.
     *
     * @param CredentialsInterface $credentials AccessControl credentials like 'email' and 'password'.
     *
     * @return UserInterface Authenticated user.
     * @throws AuthenticationException If login fails.
     *
     * @technical Validates user credentials using the guard's `attempt` method. If validation succeeds,
     *            it retrieves and returns the authenticated user. Throws an exception for invalid credentials.
     * @business  Allows a user to log in by providing valid login details (e.g., email and password),
     *            ensuring secure access to the system.
     */
    public function login(CredentialsInterface $credentials) : UserInterface
    {
        if (! $this->identity->attempt(credentials: $credentials)) {
            throw new AuthenticationException(message: 'Invalid credentials provided.');
        }

        return $this->identity->user();
    }

    /**
     * Retrieves the currently authenticated user.
     *
     * @return UserInterface|null Authenticated user or null if not authenticated.
     *
     * @technical Delegates to the guard to fetch the user currently associated with the session.
     *            Returns null if no session exists or the user is not logged in.
     * @business  Provides user-related information to interact with the system while logged in (e.g., username, roles).
     */
    public function user() : UserInterface|null
    {
        return $this->identity->user();
    }

    /**
     * Checks if a user is currently authenticated.
     *
     * @return bool True if authenticated, otherwise false.
     *
     * @technical Uses the guard's `check` method to verify if an active session exists and is associated with an
     *            authenticated user.
     * @business  Confirms whether a user is logged in, allowing the system to personalize the experience or restrict
     *            access.
     */
    public function check() : bool
    {
        return $this->identity->check();
    }

    /**
     * Logs out the authenticated user.
     *
     * @technical Calls the guard's `logout` method to clear the authenticated user's session and invalidate their
     *            tokens.
     * @business  Ends a user's session, securely logging them out to prevent unauthorized use of their account.
     */
    public function logout() : void
    {
        $this->identity->logout();
    }

    /**
     * Invalidates the current user session and clears authentication data.
     *
     * @technical Invokes the guard's `invalidate` method to destroy the current session, erasing all stored
     *            authentication information.
     * @business  Ensures that a user's session is completely destroyed, preventing misuse or unauthorized access after
     *            logout.
     */
    public function invalidateSession() : void
    {
        $this->identity->invalidate();
    }
}