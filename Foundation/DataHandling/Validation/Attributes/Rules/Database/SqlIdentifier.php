<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules\Database;

use Attribute;
use Avax\Exceptions\ValidationException;

/**
 * Validates that a value is a valid SQL identifier (table or column name).
 *
 * It allows literal alphanumeric characters and underscores, with an optional dot separator
 * for schema.table or table.column notation.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY)]
readonly class SqlIdentifier
{
    public function __construct(
        private string $message = 'The :attribute must be a valid SQL identifier (alphanumeric, underscore, optional dot).'
    ) {}

    /**
     * @throws ValidationException
     */
    public function validate(mixed $value, string $property): void
    {
        if (! is_string(value: $value) || ! preg_match(pattern: '/^[a-zA-Z0-9_]+(?:\.[a-zA-Z0-9_]+)?$/', subject: $value)) {
            throw new ValidationException(
                message: str_replace(search: ':attribute', replace: $property, subject: $this->message)
            );
        }
    }
}
