<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\PublicSurface\Read;

use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheContract;
use Avax\Components\Application\Cache\System\PublicSurface\Exception\CompiledNotConfigured;
use Avax\Components\Application\Cache\System\PublicSurface\Facade\CacheRegistry;

readonly class ReadFromCache
{
    private CacheRegistry $cacheRegistry;

    private CompiledCacheContract|null $compiledCacheContract;

    public function __construct(
        CacheRegistry              $runtimeCaches,
        CompiledCacheContract|null $compiledCache = null,
    )
    {
        $this->cacheRegistry         = $runtimeCaches;
        $this->compiledCacheContract = $compiledCache;
    }

    public function read(CacheReadTarget|string $target, mixed $default = null) : mixed
    {
        if (is_string($target)) {
            return $this->cacheRegistry->default()->get($target, $default);
        }

        if ($target instanceof RuntimeCacheTarget) {
            return $this->readRuntime(target: $target);
        }

        if ($target instanceof CompiledCacheTarget) {
            return $this->readCompiled(target: $target);
        }

        throw new CompiledNotConfigured();
    }

    private function readRuntime(RuntimeCacheTarget $target) : mixed
    {
        $cache = $target->store === null
            ? $this->cacheRegistry->default()
            : $this->cacheRegistry->get($target->store);

        return $cache->get($target->key, $target->default);
    }

    private function readCompiled(CompiledCacheTarget $target) : mixed
    {
        if ($this->compiledCacheContract === null) {
            throw new CompiledNotConfigured();
        }

        return $this->compiledCacheContract->read(
            name   : $target->name,
            build  : $target->builder(),
            sources: $target->sources,
        );
    }
}
