<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\Source\ProtectCacheSource;

interface CacheLockStore
{
    public function acquire(string $key, string $owner, int $ttlSeconds) : bool;

    public function release(string $key, string $owner) : void;

    public function isAcquired(string $key) : bool;

    public function getOwner(string $key) : CacheLockOwner|null;
}
