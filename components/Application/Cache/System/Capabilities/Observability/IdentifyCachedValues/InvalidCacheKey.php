<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues;

use InvalidArgumentException;
use Throwable;

final class InvalidCacheKey extends InvalidArgumentException
{
    public function __construct(
        string     $message,
        public readonly string|null $key = null,
        Throwable|null $throwable = null,
    )
    {
        parent::__construct(message: $message, code: 0, previous: $throwable);
    }
}
