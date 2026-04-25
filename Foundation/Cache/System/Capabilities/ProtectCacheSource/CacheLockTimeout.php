<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ProtectCacheSource;

final readonly class CacheLockTimeout
{
    public function __construct(
        public int $seconds = 5
    ) {}

    public function isExpired(int $acquiredAt) : bool
    {
        return (time() - $acquiredAt) > $this->seconds;
    }

    public function inMilliseconds() : int
    {
        return $this->seconds * 1000;
    }
}