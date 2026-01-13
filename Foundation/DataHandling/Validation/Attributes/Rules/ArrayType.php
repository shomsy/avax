<?php

declare(strict_types=1);

/**
 * Attribute to enforce that a property value must be an array.
 *
 * Annotated properties treated with this attribute will have
 * their values validated to ensure they are arrays. If not,
 * an exception is thrown to signal a validation failure.
 *
 * To be used specifically on property level.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * Attribute class intended to enforce array type validation for properties.
 *
 * This class is designed to ensure that certain properties, when decorated with
 * this attribute, must hold array values. It integrates with the validation
 * mechanism throwing exceptions when validation fails.
 *
 * Decorate properties in DTOs with this class to enforce type constraints and
 * keep data integrity.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class ArrayType
{
    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property): void
    {
        if (! is_array(value: $value)) {
            throw new ValidationException(message: $property.' must be an array.');
        }
    }
}
