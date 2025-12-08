<?php

declare(strict_types=1);

/**
 * The Distinct attribute is used to ensure that all elements in a property array are unique.
 * It is applied to a property using the PHP attribute syntax.
 *
 * @Attribute(flags: Attribute::TARGET_PROPERTY)
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * Attribute class to enforce uniqueness constraint on property values.
 * This class uses the Attribute flag TARGET_PROPERTY to specify that it
 * should be used on class properties. Ensures that an array property
 * contains unique elements only.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class Distinct
{
    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        if (count($value) !== count(array_unique($value))) {
            throw new ValidationException(message: $property . ' field has a duplicate value.');
        }
    }
}
