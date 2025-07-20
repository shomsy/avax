<?php

declare(strict_types=1);

namespace Gemini\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Gemini\Database\Migration\Design\Table\Enum\FieldModifierEnum;
use Gemini\Exceptions\ValidationException;

/**
 * Validates and casts an array of migration field modifiers.
 *
 * Accepts:
 * - FieldModifierEnum[]
 * - string[] matching FieldModifierEnum values
 *
 * Rejects:
 * - any non-array input
 * - values not resolvable via FieldModifierEnum::tryFrom()
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
readonly class MigrationFieldAttributesRule
{
    /**
     * Validates an array of enum-compatible modifiers.
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

        if (! is_array($value)) {
            throw new ValidationException(
                message: "{$property} must be an array of FieldModifierEnum values or string equivalents."
            );
        }

        foreach ($value as $item) {
            if ($item === null) {
                continue;
            }

            if ($item instanceof FieldModifierEnum) {
                continue;
            }

            if (! is_string($item)) {
                throw new ValidationException(
                    message: "{$property} contains non-string value: " . var_export($item, true)
                );
            }

            if (! FieldModifierEnum::tryFrom($item)) {
                throw new ValidationException(
                    message: "{$property} contains invalid field modifier: " . var_export($item, true)
                );
            }
        }
    }

    public function apply(mixed $value) : array|null
    {
        return is_array($value) ? $value : null;
    }

}
