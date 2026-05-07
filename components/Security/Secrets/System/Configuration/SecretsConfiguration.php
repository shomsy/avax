<?php

declare(strict_types=1);

namespace Avax\Components\Security\Secrets\System\Configuration;

final readonly class SecretsConfiguration
{
    public function __construct(
        public string $defaultStore = 'memory',
        public bool   $encryptAtRest = true,
        public string $redactionMask = '***',
        public int    $maxSecretLength = 65536,
    ) {}
}
