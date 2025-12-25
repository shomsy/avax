<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * Validates and casts to array from native array or JSON stringified array.
 *
 * Accepts:
 * - native PHP array
 * - JSON-encoded array string
 * - null
 *
 * Rejects:
 * - all other types (objects, ints, resources, strings that are not valid JSON arrays)
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class MigrationArrayRule
{
    /**
     * Validates that the value is either a PHP array or a valid JSON string representing an array.
     *
     * @param mixed  $value
     * @param string $property
     *
     * @throws ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        if ($value === null) {
            return;
        }

        if (is_array(value: $value)) {
            return;
        }

        if (is_string(value: $value) && json_validate(json: $value)) {
            $decoded = json_decode(json: $value, associative: true);

            if (is_array(value: $decoded)) {
                return;
            }
        }

        throw new ValidationException(
            message: "{$property} must be a valid array or JSON array string. Got: " . get_debug_type(value: $value)
        );
    }

    /**
     * Converts value to PHP array if valid. Returns null if value is null.
     *
     * @param mixed $value
     *
     * @return array|null
     */
    public function apply(mixed $value) : mixed
    {
        return $value;
    }
}
