<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\AdminRealm;

use RuntimeException;

final class AdminElevationFailed extends RuntimeException
{
    public static function unauthenticated() : self
    {
        return new self('Authentication is required.');
    }

    public static function forbidden() : self
    {
        return new self('Admin elevation requires an administrator.');
    }

    public static function notElevated() : self
    {
        return new self('Admin elevation is required.');
    }

    public static function missingBinding() : self
    {
        return new self('Current authentication cannot be elevated.');
    }
}
