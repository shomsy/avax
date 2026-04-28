<?php

declare(strict_types=1);

namespace Avax\Components\Auth\System\Capabilities\Identity\Tokens\Runtime\Flow;

use RuntimeException;

final class RefreshAuthenticationFailed extends RuntimeException
{
    public static function invalidToken() : self
    {
        return new self(message: 'The provided refresh token is invalid, expired, or already used.');
    }
}
