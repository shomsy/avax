<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Configuration;

final class SessionSecurityConfig
{
    public function __construct(
        public readonly ?string $encryptionKey = null,
        public readonly ?string $signSecret = null,
        public readonly bool    $encrypt = false,
        public readonly bool    $sign = false,
        public readonly int     $idleTimeout = 1800,
        public readonly int     $absoluteLifetime = 86400,
        public readonly bool    $regenerateId = true
    ) {}
}