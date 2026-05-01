<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;





final readonly class CheckCompiledCacheIsFresh
{
    private CompiledCacheDirectory $compiledCacheDirectory;

    private CompiledCacheManifest $compiledCacheManifest;

    public function __construct(
        CompiledCacheDirectory $directory,
        CompiledCacheManifest  $manifest,
    )
    {
        $this->compiledCacheDirectory = $directory;
        $this->compiledCacheManifest  = $manifest;
    }

    public function requiresRebuild(CompiledCacheName $name, CompiledCacheSources $sources) : bool
    {
        return $this->check(name: $name, sources: $sources) !== CompiledCacheFreshness::FRESH;
    }

    public function check(CompiledCacheName $name, CompiledCacheSources $sources) : CompiledCacheFreshness
    {
        $resolveCompiledCachePath = new ResolveCompiledCachePath(directory: $this->compiledCacheDirectory);
        $compiledCachePath = $resolveCompiledCachePath->resolveArtifactPath(name: $name);

        if (! file_exists($compiledCachePath->toString())) {
            return CompiledCacheFreshness::MISSING;
        }

        if (! $this->compiledCacheManifest->has(name: $name->toString())) {
            return CompiledCacheFreshness::STALE;
        }

        if (! $this->compiledCacheManifest->isFresh(name: $name->toString(), sources: $sources)) {
            return CompiledCacheFreshness::STALE;
        }

        return CompiledCacheFreshness::FRESH;
    }
}
