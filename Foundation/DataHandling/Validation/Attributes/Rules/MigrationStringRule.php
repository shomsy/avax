<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * Validates and casts a value to string if allowed.
 *
 * Accepts:
 * - native string
 * - int, float, bool (castable scalars)
 * - objects implementing __toString()
 * Rejects:
 * - arrays, resources, objects without __toString
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class MigrationStringRule
{
    /**
     * Validates the string-compatibility of a value.
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

        if (is_bool($value)) {
            throw new ValidationException(message: "{$property} cannot be a boolean when casting to string.");
        }

        if (! is_scalar($value) && ! (is_object($value) && method_exists($value, '__toString'))) {
            throw new ValidationException(
                message: "{$property} must be a string or string-castable object. Got: " . get_debug_type($value)
            );
        }
    }

    public function apply(mixed $value) : mixed
    {
        return $value;
    }
}
