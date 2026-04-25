<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Configuration;

use SensitiveParameter;

final class SessionSecurityConfig
{
    public function __construct(
        public readonly string|null                       $encryptionKey = null,
        #[SensitiveParameter] public readonly string|null $signSecret = null,
        public readonly bool                              $encrypt = false,
        public readonly bool                              $sign = false,
        public readonly int                               $idleTimeout = 1800,
        public readonly int                               $absoluteLifetime = 86400,
        public readonly bool                              $regenerateId = true
    ) {}
}