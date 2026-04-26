<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionSecurity\SessionEncryption;

use Random\RandomException;

final class SessionEncryptionKey
{
    /**
     * @throws RandomException
     */
    public static function generate() : string
    {
        return bin2hex(random_bytes(32));
    }
}