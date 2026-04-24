<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionCookie;

final class SessionCookie
{
    private string $name;
    private string $value;
    private int    $expires;
    private string $path;
    private string $domain;
    private bool   $secure;
    private bool   $httpOnly;

    public function __construct(
        string $name = 'SID',
        string $value = '',
        int    $expires = 0,
        string $path = '/',
        string $domain = '',
        bool   $secure = false,
        bool   $httpOnly = true
    )
    {
        $this->name     = $name;
        $this->value    = $value;
        $this->expires  = $expires;
        $this->path     = $path;
        $this->domain   = $domain;
        $this->secure   = $secure;
        $this->httpOnly = $httpOnly;
    }

    public function send() : void
    {
        $params = [
            'expires'  => $this->expires,
            'path'     => $this->path,
            'domain'   => $this->domain,
            'secure'   => $this->secure,
            'httponly' => $this->httpOnly,
            'samesite' => 'Lax',
        ];

        setcookie($this->name, $this->value, $params);
    }

    public function name() : string
    {
        return $this->name;
    }

    public function value() : string
    {
        return $this->value;
    }
}