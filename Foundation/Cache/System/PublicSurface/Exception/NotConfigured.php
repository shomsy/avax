<?php

declare(strict_types=1);

namespace Avax\Cache\System\PublicSurface\Exception;

use RuntimeException;
use Throwable;

final class NotConfigured extends RuntimeException
{
    public function __construct(
        string         $message = 'No default cache configured',
        Throwable|null $previous = null
    )
    {
        parent::__construct(message: $message, code: 0, previous: $previous);
    }
}