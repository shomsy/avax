<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\ProtectCacheSource;

interface CacheLockStore
{
    public function acquire(string $key, int $ttlSeconds = 30, ?string $owner = null): bool;

    public function release(string $key, ?string $owner = null): void;

    public function isAcquired(string $key): bool;

    public function getOwner(string $key): ?CacheLockOwner;
}
