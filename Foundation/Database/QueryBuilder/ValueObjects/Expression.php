<?php

declare(strict_types=1);

namespace Avax\Database\QueryBuilder\ValueObjects;

use Stringable;

/**
 * @see https://github.com/shomsy/components/blob/main/Foundation/Database/docs/DSL/RawExpressions.md

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
