<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionSecurity\SessionId;

use Random\RandomException;

final class SessionIdGenerator
{
    /**
     * @throws RandomException
     */
    public function generate() : string
    {
        return bin2hex(random_bytes(32));
    }
}