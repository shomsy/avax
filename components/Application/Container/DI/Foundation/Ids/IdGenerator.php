<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\DI\Foundation\Ids;

use Random\RandomException;

/**
 * Generates short opaque identifiers for diagnostics and correlation.
 */
final class IdGenerator
{
    /**
     * Creates one new identifier.
     *
     * @throws RandomException
     */
    public function next(string $prefix = '') : string
    {
        return $prefix . bin2hex(string: random_bytes(length: 8));
    }
}
