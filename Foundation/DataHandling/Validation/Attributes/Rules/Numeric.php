<?php

declare(strict_types=1);

/**
 * Attribute to enforce numeric validation on a property.
 *
 * This attribute is applied to properties that must contain numeric values.
 * The `validate` method will be called to ensure the property value adheres to the numeric constraint.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * Attribute class used to enforce that a property must be numeric.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class Numeric
{
    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        if (! is_numeric($value)) {
            throw new ValidationException(message: $property . ' must be a number.');
        }
    }
}
