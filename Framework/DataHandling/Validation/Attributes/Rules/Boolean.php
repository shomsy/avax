<?php

declare(strict_types=1);

/**
 * A custom attribute to validate boolean values.
 *
 * This attribute is applied to properties within Data Transfer Objects (DTOs). The validation ensures that the
 * property
 * value adheres to a boolean format. The attribute itself helps enforce data consistency and integrity, typically in
 * data handling and transfer scenarios.
 *
 * Note: The #[Attribute(flags: Attribute::TARGET_PROPERTY)] syntax ensures this attribute can only be applied to
 * properties.
 */

namespace Gemini\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Gemini\Exceptions\ValidationException;

/**
 * Validates that the provided value is a boolean.
 * Throws a ValidationException if the value is not a boolean.
 *
 * The use of filter_var with FILTER_VALIDATE_BOOLEAN and FILTER_NULL_ON_FAILURE
 * ensures that we are only accepting true or false values. This is important
 * as some values (e.g., "yes", "no") might incorrectly pass simple boolean checks.
 *
 * The rationale here is to provide strict validation for a boolean context,
 * ensuring data consistency and avoiding potential bugs arising from loosely
 * validated values.
 *
 * @throws \Gemini\Exceptions\ValidationException if the value is not a boolean.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class Boolean
{
    /**
     * @throws \Gemini\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        if (! is_bool(filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE))) {
            throw new ValidationException(message: $property . ' field must be true or false.');
        }
    }
}
