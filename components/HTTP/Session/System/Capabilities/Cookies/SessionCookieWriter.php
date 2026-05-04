<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Capabilities\Cookies;

use Avax\Components\HTTP\Session\System\Configuration\SessionCookieSettings;

final readonly class SessionCookieWriter
{
    public function __construct(
        private SessionCookieSettings $sessionCookieSettings,
    ) {}

    public function write(string $sessionId) : void
    {
        $expires = $this->sessionCookieSettings->lifetime > 0 ? time() + $this->sessionCookieSettings->lifetime : 0;

        setcookie($this->sessionCookieSettings->name, $sessionId, [
            'expires' => $expires,
            'path'     => $this->sessionCookieSettings->path,
            'domain'   => $this->sessionCookieSettings->domain,
            'secure'   => $this->sessionCookieSettings->secure,
            'httponly' => $this->sessionCookieSettings->httpOnly,
            'samesite' => $this->sessionCookieSettings->sameSite,
        ]); // @phpstan-ignore-line
    }

    public function expire() : void
    {
        setcookie($this->sessionCookieSettings->name, '', [
            'expires' => time() - 3600,
            'path'     => $this->sessionCookieSettings->path,
            'domain'   => $this->sessionCookieSettings->domain,
            'secure'   => $this->sessionCookieSettings->secure,
            'httponly' => $this->sessionCookieSettings->httpOnly,
            'samesite' => $this->sessionCookieSettings->sameSite,
        ]); // @phpstan-ignore-line
    }
}
