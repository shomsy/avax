<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\ValueObjects;

use Stringable;

/**
 * Technical representation of a raw SQL expression which must bypass standard escaping.
 *
 * -- intent:
 * Provides a secure, intentional mechanism for developers to inject literal 
 * SQL fragments (e.g., function calls, complex math) into the builder 
 * while signaling to the Grammar that the content is trusted and pre-formatted.
 *
 * -- invariants:
 * - The object must be strictly immutable.
 * - The value must be returned as-is by the SQL compilation engine.
 * - Content is assumed to be safe/sanitized by the producer (Escape Hatch).
 *
 * -- boundaries:
 * - Does NOT perform character escaping or parameterization.
 * - Does NOT validate the syntactical correctness of the SQL fragment.
 */
final readonly class Expression implements Stringable
{
    /**
     * @param string $value The raw technical SQL fragment to be injected literally.
     */
    public function __construct(public string $value) {}

    /**
     * Retrieve the internal raw SQL instruction as a primitive string.
     *
     * -- intent:
     * Support seamless integration with string-based operations and 
     * concatenation during SQL compilation.
     *
     * @return string The raw SQL instruction.
     */
    public function __toString(): string
    {
        return $this->value;
    }

    /**
     * Retrieve the encapsulated raw SQL value.
     *
     * -- intent:
     * Provide an explicit getter for retrieving the raw instruction, 
     * typically consumed by the Grammar technician.
     *
     * @return string The raw SQL fragment.
     */
    public function getValue(): string
    {
        return $this->value;
    }
}
