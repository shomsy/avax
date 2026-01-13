<?php

declare(strict_types=1);

namespace Avax\DataHandling\Validation\Attributes\Rules\Database\Query;

use Attribute;
use Avax\DataHandling\Validation\Attributes\AbstractRule;

/**
 * Validates that a value is a valid SQL column identifier.
 *
 * It allows:
 * - Alphanumeric characters and underscores.
 * - Dot separator (table.column).
 * - Wildcard asterisk (*) or table.* notation.
 */
#[Attribute(flags: Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER | Attribute::TARGET_METHOD)]
class IsValidColumnName extends AbstractRule
{
    public function __construct(
        private readonly string $message = 'The :attribute must be a valid column name (alphanumeric, underscore, optional dot, wildcard).'
    ) {}

    public function validate(mixed $value, array $data, string $property): void
    {
        if (! is_string(value: $value)) {
            $this->fail(message: $this->message, property: $property);
        }

        // Allow wildcard '*'
        if ($value === '*') {
            return;
        }

        // Allow 'table.*'
        if (preg_match(pattern: '/^[a-zA-Z0-9_]+\.\*$/', subject: $value)) {
            return;
        }

        // Standard column name validation (same as table name)
        if (! preg_match(pattern: '/^[a-zA-Z0-9_]+(?:\.[a-zA-Z0-9_]+)?$/', subject: $value)) {
            $this->fail(message: str_replace(search: ':attribute', replace: $property, subject: $this->message), property: $property);
        }
    }
}
