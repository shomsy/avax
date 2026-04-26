<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Source\ProtectCacheSource;

use Avax\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use RuntimeException;
use Throwable;

final class CacheLockWasNotAcquired extends RuntimeException
{
    public function __construct(
        string          $message,
        public CacheKey $key,
        public int      $timeoutSeconds,
        Throwable|null  $previous = null
    )
    {
        parent::__construct(message: $message, code: 0, previous: $previous);
    }

    public static function timeout(CacheKey $key, int $timeoutSeconds) : self
    {
        return new self(
            message       : sprintf(
                                'Lock for key "%s" could not be acquired after %d seconds',
                                $key->fullKey(),
                                $timeoutSeconds
                            ),
            key           : $key,
            timeoutSeconds: $timeoutSeconds
        );
    }
}