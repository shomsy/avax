<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;
use InvalidArgumentException;

/**
 * Attribute for enforcing regular expression validation on class properties.
 *
 * Validates that a property value matches a specified regular expression.
 * Includes optional custom error messages for flexible validation error handling.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class RegexException
{
    private const string DEFAULT_ERROR_MESSAGE = '%s format is invalid.';

    /**
     * Constructor for the RegexException attribute.
     *
     * @param string      $pattern The regular expression pattern to validate against.
     * @param string|null $message Optional custom error message for validation failures.
     *
     * @throws InvalidArgumentException If the provided regex pattern is invalid.
     */
    public function __construct(
        private string      $pattern,
        private string|null $message = null
    ) {
        $this->validatePattern($pattern);
    }

    /**
     * Ensures the regex pattern is valid.
     *
     * @param string $pattern The regex pattern to validate.
     *
     * @throws InvalidArgumentException If the regex pattern is invalid.
     */
    private function validatePattern(string $pattern) : void
    {
        if (preg_match($pattern, '') === false) {
            throw new InvalidArgumentException(sprintf('Invalid regex pattern: %s', $pattern));
        }
    }

    /**
     * Validates a value against the regex pattern.
     *
     * @param mixed  $value    The value to validate.
     * @param string $property The name of the property being validated.
     *
     * @throws ValidationException If the value does not match the regex pattern.
     */
    public function validate(mixed $value, string $property) : void
    {
        if ($this->isInvalidValue($value)) {
            throw new ValidationException(
                message : $this->message ?? sprintf(self::DEFAULT_ERROR_MESSAGE, $property),
                metadata: [
                              'property' => $property,
                              'value'    => $value,
                              'pattern'  => $this->pattern,
                          ]
            );
        }
    }

    /**
     * Checks if a value is invalid based on the regex pattern.
     *
     * @param mixed $value The value to check.
     *
     * @return bool True if the value is invalid; false otherwise.
     */
    private function isInvalidValue(mixed $value) : bool
    {
        if (! is_string($value) && ! is_numeric($value)) {
            return true; // Only strings and numeric values are valid
        }

        return preg_match($this->pattern, (string) $value) !== 1;
    }
}
