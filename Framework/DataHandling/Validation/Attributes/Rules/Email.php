<?php

declare(strict_types=1);

/**
 * Attribute class to mark a property as needing email validation.
 *
 * This custom attribute can be used on property declarations to enforce
 * email validation rules, encapsulating the validation logic in a reusable manner.
 *
 * Example:
 *
 * #[Email]
 * private string $email;
 */

namespace Gemini\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Gemini\Exceptions\ValidationException;

/**
 * Validates whether the provided value is a valid email address.
 *
 * @throws \Gemini\Exceptions\ValidationException if the value is not a valid email address.
 *
 * The rationale for this approach is to ensure that only valid email addresses are accepted and
 * stored within the system. By enforcing this validation at the point where the attribute is used,
 * it provides a centralized validation mechanism that ensures consistency across different parts
 * of the application where the Email attribute is applied.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class Email
{
    /**
     * @throws \Gemini\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        if (! filter_var($value, FILTER_VALIDATE_EMAIL)) {
            throw new ValidationException(message: $property . ' must be a valid email address.');
        }
    }
}
