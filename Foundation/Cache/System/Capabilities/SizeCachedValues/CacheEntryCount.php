<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\SizeCachedValues;

readonly class CacheEntryCount
{
    public function __construct(
        public int $count
    ) {}

    public static function zero() : self
    {
        return new self(0);
    }

    public function increment() : self
    {
        return new self($this->count + 1);
    }

    public function decrement() : self
    {
        return new self(max(0, $this->count - 1));
    }

    public function isZero() : bool
    {
        return $this->count === 0;
    }
}