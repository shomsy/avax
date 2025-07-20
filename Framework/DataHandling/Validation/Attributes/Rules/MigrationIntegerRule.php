<?php

declare(strict_types=1);

namespace Gemini\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Gemini\Exceptions\ValidationException;

/**
 * Validates and casts integer-compatible inputs.
 *
 * Accepts:
 * - Native integer (e.g. 42)
 * - Numeric strings representing non-negative integers (e.g. "42")
 *
 * Rejects:
 * - Floats (e.g. 3.14)
 * - Negative numeric strings with non-digit chars (e.g. "-42", "42a")
 * - Booleans, arrays, objects, null (except null is accepted)
 *
 * Example:
 *   "42" => 42
 *   42 => 42
 *   null => null
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class MigrationIntegerRule
{
    /**
     * Validates the value against integer or digit-only string formats.
     *
     * @param mixed  $value
     * @param string $property
     *
     * @throws ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        if ($value === null || is_int($value)) {
            return;
        }

        if (is_string($value) && preg_match('/^\d+$/', $value)) {
            return;
        }

        throw new ValidationException(
            message: "{$property} must be an integer or numeric string. Got: " . get_debug_type($value)
        );
    }

    public function apply(mixed $value) : mixed
    {
        return $value;
    }
}
