<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Database\System\Capabilities\Migrations\Design\Enums\FieldType;
use Avax\Exceptions\ValidationException;

/**
 * Validation rule for the 'type' field in FieldDTO.
 *
 * Ensures the field is either:
 * - an instance of FieldType (hydrated previously), or
 * - null (optional field)
 *
 * No casting is done here – hydration must have resolved the correct type.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class MigrationFieldTypeRule
{
    /**
     * Validates the 'type' field value without casting.
     *
     * @param mixed  $value    The raw or hydrated value of the property
     * @param string $property The property name being validated
     *
     * @throws ValidationException If the value is not null or a FieldType instance
     */
    public function validate(mixed $value, string $property) : void
    {
        if ($value === null) {
            return;
        }

        if ($value instanceof FieldType) {
            return;
        }

        if (is_string(value: $value) && FieldType::fromInput(value: $value) !== null) {
            return;
        }

        if (! $value instanceof FieldType) {
            throw new ValidationException(
                message: "{$property} must be a valid FieldType value or null. Got: " . get_debug_type(value: $value)
            );
        }
    }

    public function apply(mixed $value) : mixed
    {
        if (is_string(value: $value)) {
            return FieldType::fromInput(value: $value);
        }

        return $value instanceof FieldType ? $value : null;
    }
}
