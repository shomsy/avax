<?php

declare(strict_types=1);

namespace Avax\Components\Security\DataProtection\System\Configuration;

final readonly class DataProtectionConfiguration
{
    public function __construct(
        public string $encryptionCipher = 'aes-256-gcm',
        public string $defaultKeyId = 'default',
        public bool   $authenticateCiphertext = true,
    ) {}

    public static function make() : self
    {
        return new self();
    }
}
