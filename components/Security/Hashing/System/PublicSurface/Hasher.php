<?php

declare(strict_types=1);

namespace Avax\Components\Security\Hashing\System\PublicSurface;

final readonly class Hasher
{
    public static function make(string $value, string $algo = 'sha256') : string
    {
        return hash($algo, $value);
    }

    public static function verify(string $value, string $hash, string $algo = 'sha256') : bool
    {
        return hash_equals(hash($algo, $value), $hash);
    }
}
