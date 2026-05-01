<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Flows\Protection\ProtectCacheSource;

use Avax\Components\Application\Cache\System\Capabilities\Source\ProtectCacheSource\CacheLock;

final readonly class StampedeLockGuard
{
    public function __construct(
        private CacheLock $cacheLock,
        private string $key,
    ) {}

    public function release() : void
    {
        $this->cacheLock->release(cacheKey $this->key)
    }

    public function key() : string
    {
        return $this->key;
    }
}
