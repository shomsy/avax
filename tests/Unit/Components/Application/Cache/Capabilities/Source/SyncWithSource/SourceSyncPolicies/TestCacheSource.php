<?php

declare(strict_types=1);

namespace Avax\Tests\Unit\Components\Application\Cache\Capabilities\Source\SyncWithSource\SourceSyncPolicies;

use Avax\Components\Application\Cache\System\Capabilities\Source\SyncWithSource\CacheSource;
use Avax\Components\Application\Cache\System\Capabilities\Source\SyncWithSource\CacheSourceKey;
use Override;

final class TestCacheSource implements CacheSource
{
    /** @var array<string, mixed> */
    private array $data = [];

    #[Override]
    public function load(CacheSourceKey $cacheSourceKey) : mixed
    {
        return $this->data[$cacheSourceKey->fullKey()] ?? null;
    }

    #[Override]
    public function write(CacheSourceKey $cacheSourceKey, mixed $value) : void
    {
        $this->data[$cacheSourceKey->fullKey()] = $value;
    }

    #[Override]
    public function delete(CacheSourceKey $cacheSourceKey) : void
    {
        unset($this->data[$cacheSourceKey->fullKey()]);
    }

    #[Override]
    public function exists(CacheSourceKey $cacheSourceKey) : bool
    {
        return isset($this->data[$cacheSourceKey->fullKey()]);
    }

    public function get(string $key) : mixed
    {
        return $this->data[$key] ?? null;
    }

    public function set(string $key, mixed $value) : void
    {
        $this->data[$key] = $value;
    }

    public function has(string $key) : bool
    {
        return isset($this->data[$key]);
    }
}
