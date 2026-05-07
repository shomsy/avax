<?php

declare(strict_types=1);

namespace Avax\Components\Security\Hashing\System\Configuration;

final readonly class HashingConfiguration
{
    public function __construct(
        public string $defaultAlgo = 'sha256',
        public int    $bcryptRounds = 12,
        public string $argon2Memory = '65536',
    ) {}
}
