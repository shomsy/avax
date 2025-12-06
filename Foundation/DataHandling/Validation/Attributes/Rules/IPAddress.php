<?php

declare(strict_types=1);

/**
 * Attribute to indicate that a property must be a valid IP address.
 *
 * This class is designed to be used as an attribute on properties within
 * Data Transfer Objects (DTOs). It validates that the given property value is a
 * valid IP address, ensuring data integrity and consistency.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * This class is designed to validate IP addresses for properties marked with the TARGET_PROPERTY attribute.
 *
 * The primary behavior of this class is to ensure that values assigned to certain properties are valid IP addresses,
 * throwing a ValidationException otherwise. This ensures data integrity and consistency across the application.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class IPAddress
{
    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        if (! filter_var($value, FILTER_VALIDATE_IP)) {
            throw new ValidationException(message: $property . ' must be a valid IP address.');
        }
    }
}
