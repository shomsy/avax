<?php

declare(strict_types=1);

/**
 * Attribute class to ensure a property contains only alphabetic characters.
 *
 * This class uses the #[Attribute] annotation to indicate that it can be used as an attribute,
 * specifically targeting properties. The validation logic enforces that the value assigned to the
 * annotated property consists solely of letters (alpha characters).
 *
 * Using this class helps in maintaining data integrity by validating properties directly at the attribute definition
 * level.
 */

namespace Gemini\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Gemini\Exceptions\ValidationException;

/**
 * Attribute class used to validate that a property contains only letters.
 * Targets properties, indicating this rule applies at the property level.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class Alpha
{
    /**
     * @throws \Gemini\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        if (in_array(preg_match('/^\pL+$/u', (string) $value), [0, false], true)) {
            throw new ValidationException(message: $property . ' must only contain letters.');
        }
    }
}
