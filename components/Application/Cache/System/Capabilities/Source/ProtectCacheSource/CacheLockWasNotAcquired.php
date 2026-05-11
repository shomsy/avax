<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\ProtectCacheSource;

use Avax\Components\Application\Cache\System\Capabilities\Observability\IdentifyCachedValues\CacheKey;
use RuntimeException;
use Throwable;

final class CacheLockWasNotAcquired extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly CacheKey $cacheKey,
        public readonly int $timeoutSeconds, Throwable|null $throwable = null,
    ) {
        parent::__construct(message: $message, code: 0, previous: $throwable);
    }

    public static function timeout(CacheKey $cacheKey, int $timeoutSeconds): self
    {
        return new self(
            message       : sprintf(
                'Lock for key "%s" could not be acquired after %d seconds',
                $cacheKey->fullKey(),
                $timeoutSeconds,
            ),
            timeoutSeconds: $timeoutSeconds,
            cacheKey      : $cacheKey,
        );
    }
}
