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

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * Validate if the given value is a valid IP address.
 * Throws an exception if validation fails.
 *
 * @throws \Avax\Exceptions\ValidationException
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class IP
{
    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property): void
    {
        if (! filter_var(value: $value, filter: FILTER_VALIDATE_IP)) {
            throw new ValidationException(message: $property.' must be a valid IP address.');
        }
    }
}
