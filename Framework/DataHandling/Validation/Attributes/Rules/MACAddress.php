<?php

declare(strict_types=1);

/**
 * Attribute class for defining a MAC address validation rule to be used on DTO properties.
 *
 * This class leverages PHP's Attribute feature introduced in PHP 8. It is intended to be
 * used as an attribute on properties in Data Transfer Objects (DTOs) to enforce that the
 * value of the property is a valid MAC address.
 */

namespace Gemini\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Gemini\Exceptions\ValidationException;

/**
 * Validate the given MAC address.
 *
 * This method uses PHP's built-in `filter_var` function to check if the input value
 * is a valid MAC address. If it's not, a `ValidationException` is thrown.
 *
 * @throws \Gemini\Exceptions\ValidationException If the given value is not a valid MAC address.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class MACAddress
{
    /**
     * @throws \Gemini\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        if (! filter_var($value, FILTER_VALIDATE_MAC)) {
            throw new ValidationException(message: $property . ' must be a valid MAC address.');
        }
    }
}
