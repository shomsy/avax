<?php

declare(strict_types=1);

namespace Avax\Auth\Contracts;

use Avax\Auth\Data\RegistrationDTO;

/**
 * Interface for a user provider that
 * abstracts the retrieval, validation, and management of user data.
 */
interface UserSourceInterface
{
    /**
     * Retrieve a user by their unique identifier.
     *
     * @param mixed $identifier The unique identifier for the user. Type can vary based on implementation.
     *
     * @return UserInterface|null The user object, or null if not found.
     */
    public function retrieveById(mixed $identifier) : UserInterface|null;

    /**
     * Retrieve a user based on a set of credentials.
     *
     * @param CredentialsInterface $credentials The set of credentials.
     *
     * @return UserInterface|null The user object if found, or null if the credentials do not match any user.
     */
    public function retrieveByCredentials(CredentialsInterface $credentials) : UserInterface|null;

    /**
     * Validate the provided credentials against the stored user credentials.
     *
     * @param UserInterface        $user        The user whose credentials are to be validated.
     * @param CredentialsInterface $credentials The credentials to validate.
     *
     * @return bool True if the credentials are valid, otherwise false.
     */
    public function validateCredentials(UserInterface $user, CredentialsInterface $credentials) : bool;

    /**
     * Creates a new user based on the provided registration data.
     */
    public function createUser(RegistrationDTO $RegistrationDTO) : UserInterface;

    /**
     * Update an existing user with the provided data.
     *
     * @param UserInterface $user The user instance to update.
     * @param array         $data Associative array containing updated user data.
     *
     * @return bool True if the update was successful, otherwise false.
     */
    public function updateUser(UserInterface $user, array $data) : bool;

    /**
     * Delete a user by their unique identifier.
     *
     * @param mixed $identifier The unique identifier for the user to delete.
     *
     * @return bool True if the user was successfully deleted, otherwise false.
     */
    public function deleteUser(mixed $identifier) : bool;
}
