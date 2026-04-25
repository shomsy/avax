<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\SyncWithSource;

use RuntimeException;
use Throwable;

final class CacheSourceFailed extends RuntimeException
{
    public function __construct(
        string                         $message,
        public readonly CacheSourceKey $sourceKey,
        ?Throwable                     $previous = null
    )
    {
        parent::__construct($message, 0, $previous);
    }

    public static function unavailable(string $reason, CacheSourceKey $key, ?Throwable $previous = null) : self
    {
        return new self(
            message  : sprintf('System source unavailable: %s', $reason),
            sourceKey: $key,
            previous : $previous
        );
    }

    public static function timeout(CacheSourceKey $key, ?Throwable $previous = null) : self
    {
        return new self(
            message  : sprintf('System source timeout for key "%s"', $key->fullKey()),
            sourceKey: $key,
            previous : $previous
        );
    }

    public static function notFound(CacheSourceKey $key, ?Throwable $previous = null) : self
    {
        return new self(
            message  : sprintf('System source key "%s" not found', $key->fullKey()),
            sourceKey: $key,
            previous : $previous
        );
    }
}