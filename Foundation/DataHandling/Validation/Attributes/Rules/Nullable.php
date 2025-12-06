<?php

declare(strict_types=1);

/**
 * This class represents an attribute that can be used to mark a property as nullable.
 *
 * By using the #[Nullable] attribute, it indicates that a property on a data transfer object (DTO)
 * can accept a null value, which is relevant in many scenarios like optional fields or partial updates.
 *
 * The use of #[Attribute(flags: Attribute::TARGET_PROPERTY)] specifies that this attribute can only
 * be applied to properties, ensuring it isn't misapplied to methods or classes.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;

/**
 * This class is used as an attribute to indicate that a property can be null.
 * The lack of validation signifies that null values are permissible, simplifying the handling of such properties.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class Nullable
{
    public function validate(mixed $value, string $property) : void
    {
        // No validation needed; the property being null is acceptable.
    }
}
