<?php

declare(strict_types=1);

namespace Avax\Components\Security\Hashing\System\Flows\HashValue;

final readonly class HashValue
{
    public function hash(string $value, string $algo = 'sha256') : string
    {
        return hash($algo, $value);
    }
}
