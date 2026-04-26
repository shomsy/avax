<?php

declare(strict_types=1);

namespace components\Cache\System\Capabilities\Storage\SizeCachedValues;

readonly class CacheEntryCount
{
    public function __construct(
        public int $count
    ) {}

    public static function zero() : self
    {
        return new self(count: 0);
    }

    public function increment() : self
    {
        return new self(count: $this->count + 1);
    }

    public function decrement() : self
    {
        return new self(count: max(0, $this->count - 1));
    }

    public function isZero() : bool
    {
        return $this->count === 0;
    }
}