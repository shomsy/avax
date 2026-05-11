<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Storage\ProtectCachedValues;

use RuntimeException;
use Throwable;

final class CachePayloadWasTampered extends RuntimeException
{
    public function __construct(
        string $message, Throwable|null $throwable = null,
    ) {
        parent::__construct(message: $message, code: 0, previous: $throwable);
    }
}
