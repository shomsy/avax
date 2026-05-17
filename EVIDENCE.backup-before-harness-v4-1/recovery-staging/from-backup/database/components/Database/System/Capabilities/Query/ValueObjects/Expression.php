<?php

declare(strict_types=1);

namespace Avax\Database\System\Capabilities\Query\ValueObjects;

use Stringable;

/**
 * @see /docs/Foundation/Database/DSL/RawExpressions.md
 */
final readonly class Expression implements Stringable
{
    public string $value;

    /**
     * @param string $value The raw technical SQL fragment to be injected literally.
     */
    public function __construct(string $value)
    {
        $this->value = $value;
    }

    /**
     * Retrieve the internal raw SQL instruction as a primitive string.
     *
     * -- intent:
     * Support seamless integration with string-based operations and
     * concatenation during SQL compilation.
     *
     * @return string The raw SQL instruction.
     */
    public function __toString() : string
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
    public function getValue() : string
    {
        return $this->value;
    }
}
