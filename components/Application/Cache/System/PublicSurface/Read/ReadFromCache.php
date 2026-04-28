<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\PublicSurface\Read;

use Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache\CompiledCacheContract;
use Avax\Components\Application\Cache\System\PublicSurface\Exception\CompiledNotConfigured;

final readonly class ReadFromCache
{
    public function __construct(
        private CacheRegistry              $runtimeCaches,
        private CompiledCacheContract|null $compiledCache = null,
    ) {}

    public function read(CacheReadTarget|string $target, mixed $default = null) : mixed
    {
        if (is_string($target)) {
            return $this->runtimeCaches->default()->get($target, $default);
        }

        return match ($target->kind()) {
            CacheReadKind::RUNTIME  => $this->readRuntime(target: $target),
            CacheReadKind::COMPILED => $this->readCompiled(target: $target),
        };
    }

    private function readRuntime(RuntimeCacheTarget $target) : mixed
    {
        $cache = $target->store === null
            ? $this->runtimeCaches->default()
            : $this->runtimeCaches->get($target->store);

        return $cache->get($target->key, $target->default);
    }

    private function readCompiled(CompiledCacheTarget $target) : mixed
    {
        if ($this->compiledCache === null) {
            throw new CompiledNotConfigured();
        }

        return $this->compiledCache->read(
            name   : $target->name,
            build  : $target->builder(),
            sources: $target->sources
        );
    }
}