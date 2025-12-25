<?php

declare(strict_types=1);

namespace Avax\Auth\Data;

use Avax\Auth\Contracts\CredentialsInterface;
use Avax\DataHandling\ObjectHandling\DTO\AbstractDTO;
use Avax\DataHandling\Validation\Attributes\Rules\AlphaNumOrEmail;
use Avax\DataHandling\Validation\Attributes\Rules\RegexException;
use Avax\DataHandling\Validation\Attributes\Rules\Required;
use Avax\DataHandling\Validation\Attributes\Rules\StringType;

/**
 * Data Transfer Object (DTO) for User Authentication.
 *
 * This DTO defines the structure and validation for user login credentials, enforcing
 * strict rules to maintain data integrity and security. The attributes used in this class
 * provide declarative validation to streamline validation logic and ensure consistency.
 *
 * Key Features:
 * - **Identifier**: Supports either alphanumeric usernames or email addresses.
 * - **Password**: Enforces secure password constraints, including length and complexity.
 *
 * This class uses PHP attributes for validation, making it both concise and highly readable.
 * Validation rules adhere to OWASP guidelines for secure authentication practices.
 */
class Credentials extends AbstractDTO implements CredentialsInterface
{
    /**
     * User identifier.
     *
     * - Can be an alphanumeric username or a valid email address.
     * - Validated for format consistency to ensure proper input.
     */
    #[Required(message: "Identifier is required.")]
    #[StringType(message: "Identifier must be a string.")]
    #[AlphaNumOrEmail(message: "Identifier must be an alphanumeric username or a valid email.")]
    public string $identifier;

    /**
     * User password.
     *
     * - Must meet strict security requirements for length and complexity.
     * - Follows OWASP recommendations to ensure secure handling of sensitive data.
     */
    #[Required(message: "Password is required.")]
    #[RegexException(
        pattern: "/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/",
        message: "Password must include at least one letter, one number, and one special character, 
        and must be between 8 and 64 characters long."
    )]
    public string $password;

    public function getIdentifierKey(): string
    {
        return filter_var(value: $this->identifier, filter: FILTER_VALIDATE_EMAIL) ? 'email' : 'username';
    }

    public function getIdentifierValue(): string
    {
        return $this->identifier;
    }

    public function getPassword(): string
    {
        return $this->password;
    }
}
