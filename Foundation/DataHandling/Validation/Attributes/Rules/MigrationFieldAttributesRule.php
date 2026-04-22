<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Database\System\Capabilities\Migrations\Design\Enums\FieldModifier;
use Avax\Exceptions\ValidationException;

/**
 * Validates and casts an array of migration field modifiers.
 *
 * Accepts:
 * - FieldModifier[]
 * - string[] matching FieldModifier values
 *
 * Rejects:
 * - any non-array input
 * - values not resolvable via FieldModifier::fromInput()
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class MigrationFieldAttributesRule
{
    /**
     * Validates an array of enum-compatible modifiers.
     *
     *
     * @throws ValidationException
     */
    public function validate(mixed $value, string $property) : void
    {
        if ($value === null) {
            return;
        }

        if (! is_array(value: $value)) {
            throw new ValidationException(
                message: "{$property} must be an array of FieldModifier values or string equivalents."
            );
        }

        foreach ($value as $item) {
            if ($item === null) {
                continue;
            }

            if ($item instanceof FieldModifier) {
                continue;
            }

            if (! is_string(value: $item)) {
                throw new ValidationException(
                    message: "{$property} contains non-string value: " . var_export(value: $item, return: true)
                );
            }

            if (FieldModifier::fromInput(value: $item) === null) {
                throw new ValidationException(
                    message: "{$property} contains invalid field modifier: " . var_export(value: $item, return: true)
                );
            }
        }
    }

    public function apply(mixed $value) : array|null
    {
        if (! is_array(value: $value)) {
            return null;
        }

        return array_map(
            callback: static fn (mixed $item) => is_string(value: $item) ? FieldModifier::fromInput(value: $item) : $item,
            array   : $value
        );
    }
}
