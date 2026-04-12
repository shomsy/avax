<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Session;

use InvalidArgumentException;

/**
 * Cookie policy applied when the native session store starts a session.
 */
final readonly class SessionCookieSettings
{
    /**
     * @param 'Lax'|'Strict'|'None' $sameSite
     */
    public function __construct(
        public bool   $secure = true,
        public bool   $httpOnly = true,
        public string $sameSite = 'Lax',
        public string $path = '/',
        public string $domain = ''
    ) {
        if (! in_array($this->sameSite, ['Lax', 'Strict', 'None'], true)) {
            throw new InvalidArgumentException('Cookie sameSite must be Lax, Strict, or None.');
        }

        if ($this->sameSite === 'None' && ! $this->secure) {
            throw new InvalidArgumentException('Cookie sameSite None requires a secure cookie.');
        }
    }
}
