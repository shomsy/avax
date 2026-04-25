<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ProtectCacheSource;

use Avax\Cache\System\Capabilities\IdentifyCachedValues\CacheKey;
use RuntimeException;
use Throwable;

final class CacheLockWasNotAcquired extends RuntimeException
{
    public function __construct(
        string          $message,
        public CacheKey $key,
        public int      $timeoutSeconds,
        ?Throwable      $previous = null
    )
    {
        parent::__construct($message, 0, $previous);
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