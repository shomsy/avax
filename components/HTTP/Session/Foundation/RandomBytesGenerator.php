<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Foundation;

use Random\RandomException;

final class RandomBytesGenerator
{
    /**
     * @throws RandomException
     */
    public function generate(int $length = 32) : string
    {
        return random_bytes($length);
    }

    /**
     * @throws RandomException
     */
    public function hex(int $length = 32) : string
    {
        return bin2hex(random_bytes($length));
    }
}