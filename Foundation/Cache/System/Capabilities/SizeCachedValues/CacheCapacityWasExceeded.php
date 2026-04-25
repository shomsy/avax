<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\SizeCachedValues;

use RuntimeException;
use Throwable;

final class CacheCapacityWasExceeded extends RuntimeException
{
    public function __construct(
        string              $message,
        public readonly int $currentCount,
        public readonly int $maxCount,
        public readonly int $currentSize,
        public readonly int $maxSize,
        ?Throwable          $previous = null
    )
    {
        parent::__construct($message, 0, $previous);
    }

    public static function entries(int $current, int $max) : self
    {
        return new self(
            message     : sprintf(
                              'System capacity exceeded: %d/%d entries',
                              $current,
                              $max
                          ),
            currentCount: $current,
            maxCount    : $max,
            currentSize : 0,
            maxSize     : 0
        );
    }

    public static function size(int $current, int $max) : self
    {
        return new self(
            message     : sprintf(
                              'System size exceeded: %d/%d bytes',
                              $current,
                              $max
                          ),
            currentCount: 0,
            maxCount    : 0,
            currentSize : $current,
            maxSize     : $max
        );
    }
}