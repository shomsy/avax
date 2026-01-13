<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules;

use Attribute;
use Avax\Database\Migration\Design\Table\Enum\ForeignActionEnum;
use Avax\Exceptions\ValidationException;

/**
 * Validates the 'onDelete' and 'onUpdate' fields as valid ForeignActionEnum values.
 *
 * Accepts:
 * - null (no validation error)
 * - ForeignActionEnum instance (direct assignment)
 * - string (cast to enum via tryFrom)
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class MigrationForeignActionRule
{
    /**
     * Validates that the input is either null or a valid ForeignActionEnum (or castable string).
     *
     *
     * @throws ValidationException If an invalid type or unknown enum case is given.
     */
    public function validate(mixed $value, string $property): void
    {
        if ($value === null || $value instanceof ForeignActionEnum) {
            return;
        }

        if (! is_string(value: $value)) {
            throw new ValidationException(message: "{$property} must be a string or ForeignActionEnum instance.");
        }

        if (ForeignActionEnum::tryFrom(value: $value) === null) {
            throw new ValidationException(
                message: "{$property} is not a valid ForeignActionEnum. Got: ".var_export(value: $value, return: true)
            );
        }
    }

    public function apply(mixed $value): mixed
    {
        return $value;
    }
}
