<?php

declare(strict_types=1);

namespace Avax\Database\Query;

/**
 * Value object representing a database identifier that has already been quoted.
 *
 * -- intent: optimize compilation by flagging identifiers that bypass the quoting technician.
 */
final readonly class QuotedIdentifier
{
    /**
     * Constructor with promoted technical name via PHP 8.3.
     *
     * -- intent: capture the raw quoted string.
     *
     * @param string $value The already quoted identifier string
     */
    public function __construct(public string $value) {}

    /**
     * Handle direct string conversion for SQL concatenation.
     *
     * -- intent: simplify SQL integration through string casting.
     *
     * @return string
     */
    public function __toString() : string
    {
        return $this->value;
    }
}


