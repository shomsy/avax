<?php

declare(strict_types=1);

namespace Avax\Database\Query;

/**
 * Immutable value object representing a pre-quoted SQL identifier.
 *
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/QueryStates.md
 */
final readonly class QuotedIdentifier
{
    public string $value;

    /**
     * @param string $value The pre-sanitized and dialect-aware quoted identifier technical string.
     */
    public function __construct(string $value) { $this->value = $value; }

    /**
     * Return the quoted identifier string.
     */
    public function __toString() : string
    {
        return $this->value;
    }
}
