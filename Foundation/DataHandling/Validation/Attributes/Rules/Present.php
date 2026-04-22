<?php

declare(strict_types=1);

/**
 * Attribute class intended to mark properties that are required to be 'present'.
 * While commonly attributes enforce validation rules, this class only ensures
 * that the marked property is flagged as existing within the data set.
 *
 * This can be used in situations where simply the presence (even null or empty)
 * signifies a valid state. The property carrying this attribute thus should be checked
 * for its existence, but no further validation on its value is performed.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;

/**
 * Attribute class that enforces the presence of a property.
 * This is typically used in situations where merely setting the property is enough to confirm its presence.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class Present
{
    public function validate(mixed $value, array $data, string $property) : void
    {
        // No validation needed; the property being set means it is present.
    }
}
