<?php

declare(strict_types=1);

namespace Avax\Cache\System\PublicSurface;

use RuntimeException;

final class CacheNotConfigured extends RuntimeException
{
    public function __construct(string $message = 'No default cache configured', ?\Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}