<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Passkey;

use RuntimeException;

final class PasskeyOperationFailed extends RuntimeException
{
    public static function unauthenticated() : self
    {
        return new self('Authentication is required.');
    }

    public static function notFound() : self
    {
        return new self('Passkey challenge or credential was not found.');
    }

    public static function expired() : self
    {
        return new self('Passkey challenge has expired.');
    }

    public static function alreadyUsed() : self
    {
        return new self('Passkey challenge has already been used.');
    }

    public static function invalidLabel() : self
    {
        return new self('Passkey label is invalid.');
    }

    public static function runtimeNotConfigured() : self
    {
        return new self('Passkey runtime is not configured.');
    }
}
