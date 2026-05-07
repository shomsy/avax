<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Credentials\System\Configuration;

final readonly class CredentialsConfiguration
{
    public function __construct(
        public string $encryptionAlgo = 'aes-256-gcm',
        public bool   $encryptAtRest = true,
        public int    $maxCredentialsPerUser = 5,
    ) {}
}
