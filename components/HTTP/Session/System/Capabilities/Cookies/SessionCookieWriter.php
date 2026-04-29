<?php
declare(strict_types=1);

namespace Avax\Components\HTTP\Session\System\Capabilities\Cookies;

use Avax\Components\HTTP\Session\System\Configuration\SessionCookieSettings;

final readonly class SessionCookieWriter
{
    public function __construct(
        private SessionCookieSettings $settings
    ) {}

    public function write(string $sessionId) : void
    {
        $expires = $this->settings->lifetime > 0 ? time() + $this->settings->lifetime : 0;

        setcookie($this->settings->name, $sessionId, [
            'expires'  => $expires,
            'path'     => $this->settings->path,
            'domain'   => $this->settings->domain,
            'secure'   => $this->settings->secure,
            'httponly' => $this->settings->httpOnly,
            'samesite' => $this->settings->sameSite,
        ]);
    }

    public function expire() : void
    {
        setcookie($this->settings->name, '', [
            'expires'  => time() - 3600,
            'path'     => $this->settings->path,
            'domain'   => $this->settings->domain,
            'secure'   => $this->settings->secure,
            'httponly' => $this->settings->httpOnly,
            'samesite' => $this->settings->sameSite,
        ]);
    }
}
