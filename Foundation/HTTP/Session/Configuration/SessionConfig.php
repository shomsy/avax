<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\Configuration;

final class SessionConfig
{
    public function __construct(
        public readonly bool    $encrypt = false,
        public readonly ?string $encryptionKey = null,
        public readonly string  $driver = 'file',
        public readonly int     $lifetime = 120,
        public readonly string  $cookieName = 'AVAX_SESSION',
    ) {}
}
