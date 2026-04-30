<?php

declare(strict_types=1);

namespace Avax\Components\DataStack\Data\System\Capabilities\Numbers;

use InvalidArgumentException;

/**
 * Percentage Value Object.
 */
final readonly class Percentage
{
    public function __construct(public float $value)
    {
        if ($value < 0 || $value > 100) {
            throw new InvalidArgumentException('Percentage must be between 0 and 100.');
        }
    }

    public function format(int $decimals = 2) : string
    {
        return number_format($this->value, $decimals) . '%';
    }
}
