<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\SyncWithSource;

interface CacheSource
{
    public function load(CacheSourceKey $cacheSourceKey): mixed;

    public function write(CacheSourceKey $cacheSourceKey, mixed $value): void;

    public function delete(CacheSourceKey $cacheSourceKey): void;

    public function exists(CacheSourceKey $cacheSourceKey): bool;
}
