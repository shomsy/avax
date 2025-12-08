<?php

declare(strict_types=1);

namespace Avax\Auth\Adapters;

use Avax\Auth\Contracts\CredentialsInterface;
use Avax\Auth\Contracts\UserInterface;
use Avax\Auth\Contracts\UserSourceInterface;
use Avax\Auth\Adapters\UserDataSource;

/**
 * Identity serves as an abstract implementation for authentication mechanisms.
 * It utilizes a UserSourceInterface to retrieve and authenticate users based on their credentials.
 * Simply -> identity is user from db
 */
abstract class Identity
{
    /**
     * Construct the Identity with a UserSourceInterface instance.
     *
     * @param User $user The provider responsible for user retrieval and data operations.
     */
    public function __construct(protected UserSourceInterface $user) {}

    /**
     * Authenticate a user based on provided credentials.
     *
     * This method retrieves a user using their credentials and verifies their password.
     * If the credentials are valid and the password matches, the user is authenticated.
     *
     * @param CredentialsInterface $credentials The credentials used to authenticate the user.
     *
     * @return UserInterface|string|null The authenticated user, or null if authentication fails.
     *
     * @throws \Exception|\Psr\SimpleCache\InvalidArgumentException If any issues arise during the authentication
     *                                                              process.
     */
    protected function authenticate(CredentialsInterface $credentials) : UserInterface|string|null
    {
        /** Retrieve the user based on the provided credentials */
        $user = $this->user->retrieveByCredentials($credentials);

        /**
         * Verify if the retrieved user's password matches the provided credentials.
         * If yes, return the user. Otherwise, return null indicating authentication failure.
         */
        if ($user instanceof UserInterface && password_verify($credentials->getPassword(), $user->getPassword())) {
            return $user;
        }

        return null;
    }
}
