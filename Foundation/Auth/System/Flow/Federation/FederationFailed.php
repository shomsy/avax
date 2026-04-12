<?php

declare(strict_types=1);

namespace Avax\Auth\System\Flow\Federation;

use RuntimeException;

final class FederationFailed extends RuntimeException
{
    public static function notFound() : self
    {
        return new self('Federation connection was not found.');
    }

    public static function runtimeNotConfigured() : self
    {
        return new self('Federation runtime is not configured.');
    }
}
