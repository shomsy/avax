<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\Source\ProtectCacheSource;

interface CacheLockStore
{
    public function acquire(string $key, int $ttlSeconds) : bool;

    public function release(string $key) : void;

    public function isAcquired(string $key) : bool;

    public function getOwner(string $key) : CacheLockOwner|null;
}