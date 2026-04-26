<?php

declare(strict_types=1);

namespace components\DataFoundation\Values\Numbers;

use components\DataFoundation\Exceptions\InvalidValueException;

/**
 * Integer greater than or equal to zero.
 */
final readonly class NonNegativeInt
{
    public function __construct(
        private int $value,
    )
    {
        if ($value < 0) {
            throw InvalidValueException::because(message: "Expected non-negative integer, got '{$value}'.");
        }
    }

    public function value() : int
    {
        return $this->value;
    }
}
