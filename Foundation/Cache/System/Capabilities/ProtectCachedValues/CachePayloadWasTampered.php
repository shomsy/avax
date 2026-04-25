<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ProtectCachedValues;

use RuntimeException;
use Throwable;

final class CachePayloadWasTampered extends RuntimeException
{
    public function __construct(
        string     $message,
        ?Throwable $previous = null
    )
    {
        parent::__construct($message, 0, $previous);
    }
}