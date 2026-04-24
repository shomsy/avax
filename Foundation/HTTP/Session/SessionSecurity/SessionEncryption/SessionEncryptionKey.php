<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionSecurity\SessionEncryption;

final class SessionEncryptionKey
{
    public static function generate() : string
    {
        return bin2hex(random_bytes(32));
    }
}