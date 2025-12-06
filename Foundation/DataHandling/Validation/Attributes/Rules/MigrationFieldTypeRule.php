<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Database\Migration\Design\Table\Enum\FieldTypeEnum;
use Avax\Exceptions\ValidationException;

/**
 * Validation rule for the 'type' field in FieldDTO.
 *
 * Ensures the field is either:
 * - an instance of FieldTypeEnum (hydrated previously), or
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
     * @throws ValidationException If the value is not null or a FieldTypeEnum instance
     */
    public function validate(mixed $value, string $property) : void
    {
        if ($value === null) {
            return;
        }

        if (! $value instanceof FieldTypeEnum) {
            throw new ValidationException(
                message: "{$property} must be an instance of FieldTypeEnum or null. Got: " . get_debug_type($value)
            );
        }
    }

    public function apply(mixed $value) : mixed
    {
        return $value;
    }
}