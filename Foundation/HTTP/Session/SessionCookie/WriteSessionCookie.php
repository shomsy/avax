<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionCookie;

final class WriteSessionCookie
{
    private $settings;

    public function __construct($settings)
    {
        $this->settings = $settings;
    }

    public function handle(string $value) : void
    {
        $cookie = new SessionCookie(
            name    : $this->settings->name,
            value   : $value,
            expires : new SessionCookieLifetime($this->settings->lifetime)->expiresAt(),
            path    : $this->settings->path,
            domain  : $this->settings->domain,
            secure  : $this->settings->secure,
            httpOnly: $this->settings->httpOnly
        );

        $cookie->send();
    }
}