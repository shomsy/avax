<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Values\Numbers;

use Avax\DataFoundation\Exceptions\InvalidValueException;

/**
 * Integer strictly greater than zero.
 */
final readonly class PositiveInt
{
    public function __construct(
        private int $value,
    )
    {
        if ($value <= 0) {
            throw InvalidValueException::because(message: "Expected positive integer, got '{$value}'.");
        }
    }

    public function value() : int
    {
        return $this->value;
    }
}
