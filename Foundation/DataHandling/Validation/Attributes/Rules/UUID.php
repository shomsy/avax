<?php

declare(strict_types=1);

/**
 * Attribute class for enforcing UUID validation on properties.
 *
 * This class is defined as an attribute which can be used to annotate properties within data transfer objects (DTOs).
 * The validation logic ensures that any property marked with this attribute contains a valid UUID string.
 */

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * This class defines a UUID attribute to be used for property validation.
 *
 * The class ensures that UUIDs conform to the standard format, making it useful for
 * database or API validations where UUIDs are commonly used as unique identifiers.
 *
 * The UUID class is marked as an attribute with the TARGET_PROPERTY flag, meaning
 * it can be assigned to class properties for validation purposes.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
class UUID
{
    private const string UUID_REGEX =
        '/^[0-9a-f]{8}-[0-9a-f]{4}-[1-5][0-9a-f]{3}-[89ab][0-9a-f]{3}-[0-9a-f]{12}$/i';

    /**
     * @throws \Avax\Exceptions\ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        if (in_array(needle: preg_match(pattern: self::UUID_REGEX, subject: (string) $value), haystack: [0, false], strict: true)) {
            throw new ValidationException(message: $property . ' must be a valid UUID.');
        }
    }
}
