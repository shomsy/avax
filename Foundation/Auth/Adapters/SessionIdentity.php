<?php

declare(strict_types=1);

namespace Avax\Auth\Adapters;

use Avax\Auth\Contracts\CredentialsInterface;
use Avax\Auth\Contracts\IdentityInterface;
use Avax\Auth\Contracts\UserInterface;
use Avax\Auth\Contracts\UserSourceInterface;
use Avax\Auth\Exceptions\AuthFailed;
use Avax\HTTP\Session\Contracts\SessionInterface;

/**
 * SessionIdentity provides authentication and session management for users using session storage.
 */
class SessionIdentity implements IdentityInterface
{
    private const string USER_KEY = 'authenticated_user_id';

    /**
     * Constructor method for the class.
     *
     * @param SessionInterface    $session      The session interface instance.
     * @param UserSourceInterface $userProvider The user provider interface instance.
     *
     * @return void
     */
    public function __construct(
        private readonly SessionInterface    $session,
        private readonly UserSourceInterface $userProvider,
    ) {}

    /**
     * Attempt to authenticate a user with the provided credentials.
     *
     * @param CredentialsInterface $credentials The user's credentials (e.g., email and password).
     *
     * @return bool True on successful authentication, false otherwise.
     * @throws AuthenticationException If authentication fails.
     * @throws AuthFailed
     * @throws AuthFailed
     */
    public function attempt(CredentialsInterface $credentials) : bool
    {
        $user = $this->userProvider->retrieveByCredentials(credentials: $credentials);

        if (! $user instanceof UserInterface) {
            throw new AuthFailed(message: 'Subject not found.');
        }

        if (! password_verify(password: $credentials->getPassword(), hash: $user->getPassword())) {
            throw new AuthFailed(message: 'Invalid credentials.');
        }

        // Session ID regeneration for security
        $this->session->regenerateId();

        // Saving the user ID in the session
        $this->session->set(key: self::USER_KEY, value: $user->getId());

        return true;
    }

    /**
     * Log out the currently authenticated user.
     */
    public function logout() : void
    {
        $this->session->delete(key: self::USER_KEY);
        $this->session->invalidate();
    }

    /**
     * Invalidates the current session.
     *
     * This method calls the session's invalidate function to end the current session.
     */
    public function invalidate() : void
    {
        $this->session->invalidate();
    }

    /**
     * Retrieve the currently authenticated user.
     *
     * @return UserInterface|null The authenticated user, or null if no user is authenticated.
     */
    public function user() : UserInterface|null
    {
        $userId = $this->session->get(key: self::USER_KEY);

        return $userId ? $this->userProvider->retrieveById(identifier: $userId) : null;
    }

    /**
     * Check if a user is currently authenticated.
     *
     * @return bool True if a user is authenticated, otherwise false.
     */
    public function check() : bool
    {
        return $this->session->has(key: self::USER_KEY);
    }
}
