<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flows\ChangeEmail;

use RuntimeException;

final class EmailChangeFailed extends RuntimeException
{
    public static function unauthenticated() : self
    {
        return new self(message: 'Authentication is required.', code: 401);
    }

    public static function invalidEmail() : self
    {
        return new self(message: 'Email change request is invalid.', code: 422);
    }

    public static function emailInUse() : self
    {
        return new self(message: 'Email address is already in use.', code: 409);
    }

    public static function invalidPassword() : self
    {
        return new self(message: 'Current password is invalid.', code: 403);
    }

    public static function invalidToken() : self
    {
        return new self(message: 'Email change token is invalid.', code: 410);
    }
}
