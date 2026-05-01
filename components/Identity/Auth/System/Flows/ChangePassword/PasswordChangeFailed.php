<?php

declare(strict_types=1);

namespace Avax\Components\Identity\Auth\System\Flows\ChangePassword;

use Exception;

/**
 * Safe password change failure contract.
 */
final class PasswordChangeFailed extends Exception
{
    public static function currentPasswordMismatch(): self
    {
        return new self(message: 'Current password is incorrect.', code: 403);
    }
}
