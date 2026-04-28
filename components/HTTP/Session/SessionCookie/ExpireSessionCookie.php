<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Session\SessionCookie;

final class ExpireSessionCookie
{
    public function handle(string $name = 'SID') : void
    {
        setcookie($name, '', [
            'expires'  => time() - 3600,
            'path'     => '/',
            'secure'   => false,
            'httponly' => true,
            'samesite' => 'Lax',
        ]);
    }
}