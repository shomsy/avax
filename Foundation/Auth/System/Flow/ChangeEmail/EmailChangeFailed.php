<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\ChangeEmail;

use RuntimeException;

final class EmailChangeFailed extends RuntimeException
{
    public static function unauthenticated() : self
    {
        return new self('Authentication is required.', 401);
    }

    public static function invalidEmail() : self
    {
        return new self('Email change request is invalid.', 422);
    }

    public static function emailInUse() : self
    {
        return new self('Email address is already in use.', 409);
    }

    public static function invalidPassword() : self
    {
        return new self('Current password is invalid.', 403);
    }

    public static function invalidToken() : self
    {
        return new self('Email change token is invalid.', 410);
    }
}
