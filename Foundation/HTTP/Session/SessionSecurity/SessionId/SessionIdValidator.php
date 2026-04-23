<?php

declare(strict_types=1);

namespace Avax\HTTP\Session\SessionSecurity\SessionId;

final class SessionIdValidator
{
    public function isValid(string $id) : bool
    {
        if (empty($id)) {
            return false;
        }

        if (strlen($id) < 32) {
            return false;
        }

        return ctype_xdigit($id);
    }
}