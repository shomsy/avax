<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Foundation;

final class RandomBytesGenerator
{
    public function generate(int $length = 32) : string
    {
        return random_bytes($length);
    }

    public function hex(int $length = 32) : string
    {
        return bin2hex(random_bytes($length));
    }
}