<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Configuration;

final readonly class SessionCookieSettings
{
    public function __construct(
        public string $name = 'SID',
        public int    $lifetime = 0,
        public string $path = '/',
        public string $domain = '',
        public bool   $secure = false,
        public bool   $httpOnly = true,
        public string $sameSite = 'Lax',
    ) {}
}
