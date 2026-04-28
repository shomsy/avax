<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\SessionCookie;

final class SessionCookieSettings
{
    public function __construct(
        public readonly string $name = 'SID',
        public readonly int    $lifetime = 0,
        public readonly string $path = '/',
        public readonly string $domain = '',
        public readonly bool   $secure = false,
        public readonly bool   $httpOnly = true,
        public readonly string $sameSite = 'Lax'
    ) {}
}