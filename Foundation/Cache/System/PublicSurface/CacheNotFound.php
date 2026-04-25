<?php

declare(strict_types=1);

namespace Avax\Cache\System\PublicSurface;

use RuntimeException;

final class CacheNotFound extends RuntimeException
{
    public function __construct(string $message = 'Cache not found in registry', ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}