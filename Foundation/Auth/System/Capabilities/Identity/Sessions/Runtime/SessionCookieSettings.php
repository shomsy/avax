<?php

declare(strict_types=1);

namespace Avax\Auth\System\Capabilities\Identity\Sessions\Runtime;

use InvalidArgumentException;

/**
 * Cookie policy applied when the native session store starts a session.
 */
final readonly class SessionCookieSettings
{
    public string $domain;
    public string $path;
    public string $sameSite;
    public bool   $httpOnly;
    public bool   $secure;

    /**
     * @param 'Lax'|'Strict'|'None' $sameSite
     */
    public function __construct(
        bool|null   $secure = null,
        bool|null   $httpOnly = null,
        string|null $sameSite = null,
        string|null $path = null,
        string      $domain = ''
    )
    {
        $secure         ??= true;
        $httpOnly       ??= true;
        $sameSite       ??= 'Lax';
        $path           ??= '/';
        $this->secure   = $secure;
        $this->httpOnly = $httpOnly;
        $this->sameSite = $sameSite;
        $this->path     = $path;
        $this->domain   = $domain;
        if (! in_array($this->sameSite, ['Lax', 'Strict', 'None'], true)) {
            throw new InvalidArgumentException(message: 'Cookie sameSite must be Lax, Strict, or None.');
        }

        if ($this->sameSite === 'None' && ! $this->secure) {
            throw new InvalidArgumentException(message: 'Cookie sameSite None requires a secure cookie.');
        }
    }
}
