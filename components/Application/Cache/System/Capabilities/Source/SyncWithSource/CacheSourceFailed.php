<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\SyncWithSource;

use RuntimeException;
use Throwable;

final class CacheSourceFailed extends RuntimeException
{
    public readonly CacheSourceKey $cacheSourceKey;

    public function __construct(
        string                         $message,
        CacheSourceKey $sourceKey,
        ?Throwable                     $throwable = null,
    )
    {
        $this->cacheSourceKey = $sourceKey;
        parent::__construct(message: $message, code: 0, previous: $throwable);
    }

    public static function unavailable(string $reason, CacheSourceKey $cacheSourceKey, Throwable|null $throwable = null) : self
    {
        return new self(
            message  : sprintf('System source unavailable: %s', $reason),
            sourceKey: $cacheSourceKey,
            throwable: $throwable,
        );
    }

    public static function timeout(CacheSourceKey $cacheSourceKey, Throwable|null $throwable = null) : self
    {
        return new self(
            message  : sprintf('System source timeout for key "%s"', $cacheSourceKey->fullKey()),
            sourceKey: $cacheSourceKey,
            throwable: $throwable,
        );
    }

    public static function notFound(CacheSourceKey $cacheSourceKey, Throwable|null $throwable = null) : self
    {
        return new self(
            message  : sprintf('System source key "%s" not found', $cacheSourceKey->fullKey()),
            sourceKey: $cacheSourceKey,
            throwable: $throwable,
        );
    }
}
