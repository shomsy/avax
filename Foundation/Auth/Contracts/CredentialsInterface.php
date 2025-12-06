<?php

declare(strict_types=1);

namespace Avax\Auth\Contracts;

/**
 * This interface is designed for defining the structure of login credentials.
 * It provides method signatures to retrieve the key and value of an identifier, as well as the password.
 * This ensures a consistent way to handle credentials across different implementations.
 */
interface CredentialsInterface
{
    /**
     * Retrieve the key used to identify the credentials (e.g., "username" or "email").
     *
     * @return string The identifier key, such as "username" or "email".
     */
    public function getIdentifierKey() : string;

    /**
     * Retrieve the actual value associated with the identifier key.
     *
     * @return string The actual identifier value.
     */
    public function getIdentifierValue() : string;

    /**
     * Retrieve the password or secret necessary for authentication.
     *
     * @return string The password or secret for authentication.
     */
    public function getPassword() : string;
}
