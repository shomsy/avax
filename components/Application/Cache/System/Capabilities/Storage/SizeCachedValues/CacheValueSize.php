<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Storage\SizeCachedValues;

readonly class CacheValueSize
{
    public function __construct(
        public int $bytes,
    ) {}

    public static function zero() : self
    {
        return new self(bytes: 0);
    }

    public function add(self $other) : self
    {
        return new self(bytes: $this->bytes + $other->bytes);
    }

    public function subtract(self $other) : self
    {
        return new self(bytes: max(0, $this->bytes - $other->bytes));
    }

    public function isZero() : bool
    {
        return $this->bytes === 0;
    }

    public function toKilobytes() : float
    {
        return $this->bytes / 1024;
    }

    public function toMegabytes() : float
    {
        return $this->bytes / 1048576;
    }
}
