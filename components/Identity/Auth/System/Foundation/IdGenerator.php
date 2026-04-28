<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Foundation;

use Random\RandomException;

/**
 * Default ID generator using random integers.
 */
final class IdGenerator implements IdGeneratorInterface
{
    /**
     * @throws RandomException
     */
    public function generate() : int
    {
        return random_int(min: 1_000_000, max: PHP_INT_MAX);
    }
}
