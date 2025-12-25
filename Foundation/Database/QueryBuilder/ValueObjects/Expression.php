<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\ValueObjects;

use Stringable;

/**
 * Represents a raw SQL expression that should not be escaped or parameterized.
 *
 * -- intent: allow developers to inject literal SQL fragments for advanced operations.
 * -- design: immutable value object implementing Stringable for seamless integration.
 */
final readonly class Expression implements Stringable
{
    /**
     * @param string $value The raw SQL fragment
     */
    public function __construct(public string $value) {}

    /**
     * Retrieve the raw SQL expression as a string.
     *
     * @return string
     */
    public function __toString() : string
    {
        return $this->value;
    }

    /**
     * Get the raw SQL value.
     *
     * @return string
     */
    public function getValue() : string
    {
        return $this->value;
    }
}
