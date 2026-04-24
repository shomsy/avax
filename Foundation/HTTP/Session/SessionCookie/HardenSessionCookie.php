<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionCookie;

final class HardenSessionCookie
{
    public function handle() : void
    {
        if (! headers_sent()) {
            header('Set-Cookie: ' . $this->name() . '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; HttpOnly; SameSite=Lax');
        }
    }

    private function name() : string
    {
        return $_SERVER['HTTP_COOKIE'] ?? 'SID';
    }
}