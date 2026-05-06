<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Database\System\Capabilities\Query\ValueObjects;

use Stringable;

/**
 * Immutable value object representing a pre-quoted SQL identifier.
 *
 * @see /docs/Foundation/Database/DSL/QueryStates.md
 */
final readonly class QuotedIdentifier implements Stringable
{
    /**
     * @param  string  $value  The pre-sanitized and dialect-aware quoted identifier technical string.
     */
    public function __construct(public string $value)
    {
    }

    /**
     * Return the quoted identifier string.
     */
    public function __toString(): string
    {
        return $this->value;
    }
}
