<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionSecurity\SessionId;

final class SessionIdGenerator
{
    public function generate() : string
    {
        return bin2hex(random_bytes(32));
    }
}