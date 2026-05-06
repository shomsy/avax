<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Observability\ObserveCache;

interface CacheTrace
{
    public function trace(CacheOperation $cacheOperation): void;

    /**
     * @return list<CacheOperation>
     */
    public function getOperations(?string $key = null): array;

    public function clear(): void;
}
