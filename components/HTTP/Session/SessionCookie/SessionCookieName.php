<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\SessionCookie;

final class SessionCookieName
{
    public function __construct(private string $value) {}

    public static function default() : self
    {
        return new self(value: 'SID');
    }

    public function value() : string
    {
        return $this->value;
    }
}