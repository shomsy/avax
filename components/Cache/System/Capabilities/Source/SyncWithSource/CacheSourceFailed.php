<?php

declare(strict_types=1);

namespace Avax\Components\Cache\System\Capabilities\Source\SyncWithSource;

use RuntimeException;
use Throwable;

final class CacheSourceFailed extends RuntimeException
{
    public function __construct(
        string                         $message,
        public readonly CacheSourceKey $sourceKey,
        Throwable|null                 $previous = null
    )
    {
        parent::__construct(message: $message, code: 0, previous: $previous);
    }

    public static function unavailable(string $reason, CacheSourceKey $key, Throwable|null $previous = null) : self
    {
        return new self(
            message  : sprintf('System source unavailable: %s', $reason),
            sourceKey: $key,
            previous : $previous
        );
    }

    public static function timeout(CacheSourceKey $key, Throwable|null $previous = null) : self
    {
        return new self(
            message  : sprintf('System source timeout for key "%s"', $key->fullKey()),
            sourceKey: $key,
            previous : $previous
        );
    }

    public static function notFound(CacheSourceKey $key, Throwable|null $previous = null) : self
    {
        return new self(
            message  : sprintf('System source key "%s" not found', $key->fullKey()),
            sourceKey: $key,
            previous : $previous
        );
    }
}