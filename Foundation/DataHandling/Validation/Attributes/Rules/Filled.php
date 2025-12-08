<?php

declare(strict_types=1);

/**
 * Attribute class to enforce that a property must have a value.
 * Applied as a property validator using attributes.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * Attribute class used to enforce that a property must have a value.
 *
 * This Attribute is intended to be used on properties to ensure they are not empty.
 * The rationale behind this class is to provide a simple way to perform validation
 * through an attribute-based validation mechanism which enhances readability and maintains
 * validation logic closer to the data definition.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class Filled
{
    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        if (empty($value)) {
            throw new ValidationException(message: $property . ' must have a value.');
        }
    }
}
