<?php

declare(strict_types=1);

namespace Avax\Components\Security\Hashing\System\Flows\VerifyHash;

final readonly class VerifyHash
{
    public function verify(string $value, string $hash, string $algo = 'sha256') : bool
    {
        return hash_equals(hash($algo, $value), $hash);
    }
}
