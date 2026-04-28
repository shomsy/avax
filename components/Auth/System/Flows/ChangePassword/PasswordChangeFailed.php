<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Flows\ChangePassword;

use Exception;

/**
 * Safe password change failure contract.
 */
class PasswordChangeFailed extends Exception
{
    public static function currentPasswordMismatch() : self
    {
        return new self(message: 'Current password is incorrect.', code: 403);
    }
}
