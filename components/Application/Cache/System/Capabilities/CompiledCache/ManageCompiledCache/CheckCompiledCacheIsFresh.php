<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

enum CompiledCacheFreshness: string
{
    case FRESH   = 'fresh';
    case STALE   = 'stale';
    case MISSING = 'missing';
}

final readonly class CheckCompiledCacheIsFresh
{
    public function __construct(
        private CompiledCacheDirectory $compiledCacheDirectory,
        private CompiledCacheManifest  $compiledCacheManifest,
    ) {}

    public function requiresRebuild(CompiledCacheName $compiledCacheName, CompiledCacheSources $compiledCacheSources) : bool
    {
        return $this->check(name: $compiledCacheName, sources: $compiledCacheSources) !== CompiledCacheFreshness::FRESH;
    }

    public function check(CompiledCacheName $compiledCacheName, CompiledCacheSources $compiledCacheSources) : CompiledCacheFreshness
    {
        $resolveCompiledCachePath = new ResolveCompiledCachePath(directory: $this->compiledCacheDirectory);
        $compiledCachePath        = $resolveCompiledCachePath->resolveArtifactPath(name: $compiledCacheName);

        if (! file_exists($compiledCachePath->toString())) {
            return CompiledCacheFreshness::MISSING;
        }

        if (! $this->compiledCacheManifest->has(name: $compiledCacheName->toString())) {
            return CompiledCacheFreshness::STALE;
        }

        if (! $this->compiledCacheManifest->isFresh(name: $compiledCacheName->toString(), sources: $compiledCacheSources)) {
            return CompiledCacheFreshness::STALE;
        }

        return CompiledCacheFreshness::FRESH;
    }
}
