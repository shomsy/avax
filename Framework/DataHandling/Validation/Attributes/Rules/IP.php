<?php

declare(strict_types=1);

/**
 * IP Attribute class used to validate that a property holds a valid IP address.
 *
 * This class is designed to be instantiated as an attribute to enforce that a property
 * within a Data Transfer Object (DTO) is a valid IP address. Utilizing PHP's Attribute
 * syntax makes the validation declarative and more maintainable.
 *
 * - The `TARGET_PROPERTY` flag ensures that this attribute can only be applied to properties.
 */

namespace Gemini\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Gemini\Exceptions\ValidationException;

/**
 * Validate if the given value is a valid IP address.
 * Throws an exception if validation fails.
 *
 * @throws \Gemini\Exceptions\ValidationException
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class IP
{
    /**
     * @throws \Gemini\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        if (! filter_var($value, FILTER_VALIDATE_IP)) {
            throw new ValidationException(message: $property . ' must be a valid IP address.');
        }
    }
}
