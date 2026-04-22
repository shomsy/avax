<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Database\System\Capabilities\Migrations\Design\Enums\ForeignAction;
use Avax\Exceptions\ValidationException;

/**
 * Validates the 'onDelete' and 'onUpdate' fields as valid ForeignAction values.
 *
 * Accepts:
 * - null (no validation error)
 * - ForeignAction instance (direct assignment)
 * - string (cast to enum via tryFrom)
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class MigrationForeignActionRule
{
    /**
     * Validates that the input is either null or a valid ForeignAction (or castable string).
     *
     *
     * @throws ValidationException If an invalid type or unknown enum case is given.
     */
    public function validate(mixed $value, string $property) : void
    {
        if ($value === null || $value instanceof ForeignAction) {
            return;
        }

        if (! is_string(value: $value)) {
            throw new ValidationException(message: "{$property} must be a string or ForeignAction instance.");
        }

        if (ForeignAction::fromInput(value: $value) === null) {
            throw new ValidationException(
                message: "{$property} is not a valid ForeignAction. Got: " . var_export(value: $value, return: true)
            );
        }
    }

    public function apply(mixed $value) : mixed
    {
        if (is_string(value: $value)) {
            return ForeignAction::fromInput(value: $value);
        }

        return $value instanceof ForeignAction ? $value : null;
    }
}
