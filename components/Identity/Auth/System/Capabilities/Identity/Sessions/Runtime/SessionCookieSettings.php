<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Capabilities\Identity\Sessions\Runtime;

use InvalidArgumentException;

/**
 * Cookie policy applied when the native session store starts a session.
 */
final readonly class SessionCookieSettings
{
    public string $path;

    public string $sameSite;

    public bool $httpOnly;

    public bool $secure;

    /**
     * @param  'Lax'|'Strict'|'None'  $sameSite
     */
    public function __construct(
        ?bool $secure = null,
        ?bool $httpOnly = null,
        ?string $sameSite = null,
        ?string $path = null,
        public string $domain = '',
    ) {
        $secure ??= true;
        $httpOnly ??= true;
        $sameSite ??= 'Lax';
        $path ??= '/';
        $this->secure = $secure;
        $this->httpOnly = $httpOnly;
        $this->sameSite = $sameSite;
        $this->path = $path;
        if (! in_array(needle: $this->sameSite, haystack: ['Lax', 'Strict', 'None'], strict: true)) {
            throw new InvalidArgumentException(message: 'Cookie sameSite must be Lax, Strict, or None.');
        }

        if ($this->sameSite === 'None' && ! $this->secure) {
            throw new InvalidArgumentException(message: 'Cookie sameSite None requires a secure cookie.');
        }
    }
}
