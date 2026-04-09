<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Register;

use Exception;

/**
 * Safe registration failure contract.
 */
class RegistrationFailed extends Exception
{
    public static function emailTaken() : self
    {
        return new self(message: 'Email is already taken.', code: 409);
    }

    public static function usernameTaken() : self
    {
        return new self(message: 'Username is already taken.', code: 409);
    }
}
