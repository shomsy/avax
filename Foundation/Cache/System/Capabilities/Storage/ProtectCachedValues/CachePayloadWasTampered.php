<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Storage\ProtectCachedValues;

use RuntimeException;
use Throwable;

final class CachePayloadWasTampered extends RuntimeException
{
    public function __construct(
        string         $message,
        Throwable|null $previous = null
    )
    {
        parent::__construct(message: $message, code: 0, previous: $previous);
    }
}