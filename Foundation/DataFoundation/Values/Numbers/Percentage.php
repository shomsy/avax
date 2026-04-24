<?php

declare(strict_types=1);

namespace Avax\DataFoundation\Values\Numbers;

use Avax\DataFoundation\Exceptions\InvalidValueException;

/**
 * Percentage constrained to the inclusive 0-100 range.
 */
final readonly class Percentage
{
    public function __construct(
        private float $value,
    )
    {
        if ($value < 0 || $value > 100) {
            throw InvalidValueException::because(message: "Percentage must be between 0 and 100, got '{$value}'.");
        }
    }

    public function value() : float
    {
        return $this->value;
    }

    public function ratio() : float
    {
        return $this->value / 100;
    }
}
