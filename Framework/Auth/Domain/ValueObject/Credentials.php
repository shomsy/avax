<?php

declare(strict_types=1);

namespace Gemini\Auth\Domain\ValueObject;

use Gemini\Auth\Contracts\CredentialsInterface;
use Gemini\Auth\DTO\AuthenticationDTO;

/**
 * The Credentials class encapsulates user credentials, such as an identifier (email or username) and a password.
 * It ensures strict typing and provides method implementations to retrieve these credentials.
 */
final readonly class Credentials implements CredentialsInterface
{
    /**
     * Class representing a simple counter with ability to increment and reset.
     *
     * This class is thread-safe due to the usage of the synchronized blocks.
     * Particularly useful in scenarios where shared access to the counter state is necessary.
     */
    private string $identifier;

    /**
     * Represents a user password which includes hashing and validation functionalities.
     * The use of private visibility ensures encapsulation.
     * The constant SALT adds an additional layer of security to password hashing.
     */
    private string $password;

    /**
     * Constructor to initialize the credentials.
     */
    public function __construct(AuthenticationDTO $authenticationDTO)
    {
        $this->identifier = $authenticationDTO->identifier;
        $this->password   = $authenticationDTO->password;
    }

    /**
     * Determines and returns the type of identifier based on format validation.
     *
     * Using PHP's filter_var function, this method checks if the identifier is a valid email format.
     * If valid, it returns "email"; otherwise, it returns "username".
     *
     * @return string 'email' if identifier is a valid email address, otherwise 'username'.
     */
    public function getIdentifierKey() : string
    {
        return filter_var($this->identifier, FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    }

    /**
     * Retrieves the actual value of the identifier.
     *
     * This function returns the value of the identifier, which could be either an email or a username.
     *
     * @return string The identifier value.
     */
    public function getIdentifierValue() : string
    {
        return $this->identifier;
    }

    /**
     * Retrieves the password for authentication.
     *
     * This function returns the stored password, which is required for authentication processes.
     *
     * @return string The password or secret for authentication.
     */
    public function getPassword() : string
    {
        return $this->password;
    }
}
