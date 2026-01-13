<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * Attribute to validate that a value is either alphanumeric or in a valid email format.
 *
 * This rule can be applied to properties of a class to ensure their value adheres to
 * the specified format. It supports usernames (alphanumeric) or valid email addresses.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class AlphaNumOrEmail
{
    /**
     * Default error message template for validating either an alphanumeric username or a valid email.
     */
    private const string DEFAULT_ERROR_MESSAGE = 'The "%s" must be either an alphanumeric username or a valid email.';

    /**
     * Constructor for the AlphaNumOrEmail attribute.
     *
     * @param string|null $message Optional custom error message.
     */
    public function __construct(private string|null $message = null) {}

    /**
     * Validates that the provided value is either alphanumeric or a valid email.
     *
     * @param mixed  $value The value to validate.
     * @param string $name  The property name being validated.
     *
     * @throws ValidationException If the value is not a valid alphanumeric string or email.
     */
    public function validate(mixed $value, string $name) : void
    {
        if (! $this->isValidValue(value: $value)) {
            throw new ValidationException(
                message : $this->message ?? sprintf(self::DEFAULT_ERROR_MESSAGE, $name),
                metadata: [
                    'property' => $name,
                    'value'    => $value,
                    'expected' => 'alphanumeric or valid email',
                ]
            );
        }
    }

    /**
     * Checks if the value is a valid alphanumeric string or email.
     *
     * @param mixed $value The value to check.
     *
     * @return bool True if the value is valid; false otherwise.
     */
    private function isValidValue(mixed $value) : bool
    {
        return is_string(value: $value) && ($this->isAlphanumeric(value: $value) || $this->isEmail(value: $value));
    }

    /**
     * Determines if the string is alphanumeric.
     *
     * @param string $value The string to check.
     *
     * @return bool True if the string is alphanumeric; false otherwise.
     */
    private function isAlphanumeric(string $value) : bool
    {
        return (bool) preg_match(pattern: '/^[a-zA-Z0-9]+$/', subject: $value);
    }

    /**
     * Determines if the string is a valid email.
     *
     * @param string $value The string to check.
     *
     * @return bool True if the string is a valid email; false otherwise.
     */
    private function isEmail(string $value) : bool
    {
        return filter_var(value: $value, filter: FILTER_VALIDATE_EMAIL) !== false;
    }
}
