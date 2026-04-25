<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\IdentifyCachedValues;

use InvalidArgumentException;
use Throwable;

final class InvalidCacheKey extends InvalidArgumentException
{
    public function __construct(
        string     $message,
        ?string    $key = null,
        ?Throwable $previous = null
    )
    {
        parent::__construct($message, 0, $previous);
    }
}