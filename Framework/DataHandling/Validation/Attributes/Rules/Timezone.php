<?php

declare(strict_types=1);

/**
 * Attribute to specify a property should hold a valid timezone.
 *
 * - Set the target to property to enforce the attribute can only be used on class properties.
 */

namespace Gemini\DataHandling\Validation\Attributes\Rules;

use Attribute;
use DateTimeZone;
use Gemini\Exceptions\ValidationException;

/**
 * Validates whether the provided value is a valid timezone identifier.
 *
 * @throws \Gemini\Exceptions\ValidationException If the value is not a valid timezone identifier.
 *
 * The method leverages the DateTimeZone::listIdentifiers() method to ensure
 * the value conforms to one of the recognized timezone identifiers. This is
 * crucial for maintaining consistency and avoiding errors related to time
 * calculations throughout the application. Any deviation triggers a
 * ValidationException.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class Timezone
{
    /**
     * @throws \Gemini\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        if (! in_array($value, DateTimeZone::listIdentifiers(), true)) {
            throw new ValidationException(message: $property . ' must be a valid timezone.');
        }
    }
}
