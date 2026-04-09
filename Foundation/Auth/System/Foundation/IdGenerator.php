<?php

declare(strict_types=1);

namespace Avax\Auth\System\Foundation;

use Random\RandomException;

/**
 * Interface for ID generation.
 */
interface IdGeneratorInterface
{
    /**
     * Generate a new unique ID.
     */
    public function generate() : int;
}

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
        return random_int(1_000_000, PHP_INT_MAX);
    }
}
