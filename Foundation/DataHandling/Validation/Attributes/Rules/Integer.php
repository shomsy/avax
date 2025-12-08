<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * Class Integer
 *
 * This class is an attribute used for validating that the targeted property is an integer.
 * It is designed to enforce validation rules declaratively using PHP's Attribute syntax
 * and ensures that incorrect data types are rejected with clear exception handling.
 *
 * Domain-Driven Design (DDD) implications:
 * - Acts as a declarative rule for property validation within domain entities or value objects.
 * - Short-circuits invalid input before further operations, preserving domain integrity.
 *
 * @package Avax\DataHandling\Validation\Attributes\Rules
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)] // Restricts this attribute to properties.
class Integer
{
    /**
     * The default error message used when the validation fails.
     *
     * This constant defines a generic error message indicating that the value must be an integer.
     * It uses a placeholder to include the property name dynamically.
     */
    private const string ERROR_MESSAGE = 'The "%s" field must be an integer.';

    /**
     * Custom error message for validation failures.
     *
     * @var string|null $message A custom message provided at instantiation to override the default.
     *                           If null, the default error message will be used.
     */
    public function __construct(private readonly string|null $message = null) {}

    /**
     * Validates that the given value is an integer.
     *
     * A property is validated against this rule. If the value does not satisfy the constraint,
     * a `ValidationException` is thrown, containing detailed metadata about the failure.
     *
     * @param mixed  $value    The value to be validated. Can be any type, as mixed is used.
     * @param string $property The name of the property being validated, for error context.
     *
     * @return void
     *
     * @throws ValidationException If the value is not an integer.
     */
    public function validate(mixed $value, string $property) : void
    {
        // Check if the value is not an integer.
        if (! is_int($value)) {
            // Throw a detailed validation exception if the value is invalid.
            throw new ValidationException(
                message : $this->message ?? sprintf(self::ERROR_MESSAGE, $property),
                // Use a custom or default error message.
                metadata: [
                              'property' => $property, // The name of the property being validated.
                              'value'    => $value,    // The actual value that failed validation.
                              'expected' => 'int',     // The expected type of the value (integer).
                              'actual'   => gettype($value), // The actual type of the provided value.
                          ]
            );
        }
    }
}