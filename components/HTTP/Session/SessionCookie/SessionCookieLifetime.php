<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionCookie;

final class SessionCookieLifetime
{
    public function __construct(private int $value) {}

    public static function default() : self
    {
        return new self(value: 0);
    }

    public function value() : int
    {
        return $this->value;
    }

    public function expiresAt() : int
    {
        return $this->value > 0 ? time() + $this->value : 0;
    }
}