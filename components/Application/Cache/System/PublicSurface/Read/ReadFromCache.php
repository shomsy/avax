<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\PublicSurface\Read;

use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheContract;
use Avax\Components\Application\Cache\System\PublicSurface\Exception\CompiledNotConfigured;
use Avax\Components\Application\Cache\System\PublicSurface\Facade\CacheRegistry;

readonly class ReadFromCache
{
    public function __construct(
        private CacheRegistry              $cacheRegistry,
        private CompiledCacheContract|null $compiledCacheContract = null,
    ) {}

    public function read(CacheReadTarget|string $target, mixed $default = null) : mixed
    {
        if (is_string($target)) {
            return $this->cacheRegistry->default()->get($target, $default);
        }

        return match ($target->kind()) {
            CacheReadKind::RUNTIME  => $this->readRuntime(target: $target),
            CacheReadKind::COMPILED => $this->readCompiled(target: $target),
        };
    }

    private function readRuntime(RuntimeCacheTarget $runtimeCacheTarget) : mixed
    {
        $cache = $runtimeCacheTarget->store === null
            ? $this->cacheRegistry->default()
            : $this->cacheRegistry->get($runtimeCacheTarget->store);

        return $cache->get($runtimeCacheTarget->key, $runtimeCacheTarget->default);
    }

    private function readCompiled(CompiledCacheTarget $compiledCacheTarget) : mixed
    {
        if ($this->compiledCacheContract === null) {
            throw new CompiledNotConfigured();
        }

        return $this->compiledCacheContract->read(
            name   : $compiledCacheTarget->name,
            build  : $compiledCacheTarget->builder(),
            sources: $compiledCacheTarget->sources,
        );
    }
}
