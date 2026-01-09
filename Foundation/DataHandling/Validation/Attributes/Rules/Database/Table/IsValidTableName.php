<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules\Database\Table;

use Attribute;
use Avax\DataHandling\Validation\Attributes\AbstractRule;
use Avax\Exceptions\ValidationException;

/**
 * Validates that a value is a valid SQL table identifier.
 *
 * It allows literal alphanumeric characters and underscores, with an optional dot separator
 * for schema.table notation.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::TARGET_METHOD)]
class IsValidTableName extends AbstractRule
{
    public function __construct(
        private readonly string $message = 'The :attribute must be a valid table name (alphanumeric, underscore, optional schema dot).'
    ) {}

    public function validate(mixed $value, array $data, string $property) : void
    {
        if (! is_string(value: $value) || ! preg_match(pattern: '/^[a-zA-Z0-9_]+(?:\.[a-zA-Z0-9_]+)?$/', subject: $value)) {
            throw new ValidationException(
                message: str_replace(search: ':attribute', replace: $property, subject: $this->message)
            );
        }
    }
}
