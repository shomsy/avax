<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Session;

/**
 * Cookie policy applied when the native session store starts a session.
 */
final readonly class SessionCookieSettings
{
    public function __construct(
        public bool   $secure = true,
        public bool   $httpOnly = true,
        public string $sameSite = 'Lax',
        public string $path = '/',
        public string $domain = ''
    ) {}
}
