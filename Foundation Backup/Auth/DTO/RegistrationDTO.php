<?php

declare(strict_types=1);

namespace Gemini\Auth\DTO;

use Gemini\DataHandling\ObjectHandling\DTO\AbstractDTO;
use Gemini\DataHandling\Validation\Attributes\Rules\AlphaNum;
use Gemini\DataHandling\Validation\Attributes\Rules\AlphaNumOrEmail;
use Gemini\DataHandling\Validation\Attributes\Rules\Max;
use Gemini\DataHandling\Validation\Attributes\Rules\Min;
use Gemini\DataHandling\Validation\Attributes\Rules\RegexException;
use Gemini\DataHandling\Validation\Attributes\Rules\Required;
use Gemini\DataHandling\Validation\Attributes\Rules\StringType;

/**
 * RegistrationDTO is designed to represent the essential data for user registration.
 *
 * It ensures strict validation of email, username, and password fields, aiming to enhance security
 * and maintain data integrity according to business requirements.
 */
class RegistrationDTO extends AbstractDTO
{
    /**
     * The user's email address.
     *
     * Validation:
     * - Required
     * - Must be a valid email or alphanumeric value.
     */
    #[Required]
    #[StringType]
    #[AlphaNumOrEmail]
    public string $email;

    /**
     * The user's alphanumeric username.
     *
     * Validation:
     * - Required
     * - Alphanumeric
     * - Minimum length: 3 characters
     */
    #[Required]
    #[StringType]
    #[Min(min: 3)]
    #[AlphaNum]
    public string $username;

    /**
     * The user's password, designed to be secure.
     *
     * Validation:
     * - Required
     * - Minimum length: 8 characters
     * - Maximum length: 64 characters
     * - Must include at least one letter, one number, and one special character.
     */
    #[Required]
    #[StringType]
    #[Min(min: 8)]
    #[Max(max: 64)]
    #[RegexException(pattern: "/^(?=.*[A-Za-z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/")]
    public string $password;

    /**
     * The user's first name.
     *
     * Validation:
     * - Required
     * - Alphanumeric
     * - Maximum length: 50 characters
     */
    #[Required]
    #[StringType]
    #[AlphaNum]
    #[Max(max: 50)]
    public string $first_name;

    /**
     * The user's last name.
     *
     * Validation:
     * - Required
     * - Alphanumeric
     * - Maximum length: 50 characters
     */
    #[Required]
    #[StringType]
    #[AlphaNum]
    #[Max(max: 50)]
    public string $last_name;

    /**
     * The user's admin status.
     *
     * Validation:
     * - Required
     * - Boolean
     *
     * Default: false
     */
    #[Required]
    public bool $is_admin = false;

    /**
     * Converts the DTO to an associative array.
     *
     * @return array<string, mixed> The data as an array.
     */
    public function toArray() : array
    {
        return [
            'email'      => $this->email,
            'username'   => $this->username,
            'password'   => $this->password,
            'first_name' => $this->first_name,
            'last_name'  => $this->last_name,
            'is_admin'   => $this->is_admin,
        ];
    }
}
