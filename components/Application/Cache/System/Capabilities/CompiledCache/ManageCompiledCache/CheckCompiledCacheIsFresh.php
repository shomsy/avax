<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;

final readonly class CheckCompiledCacheIsFresh
{
    public function __construct(
        private CompiledCacheDirectory $compiledCacheDirectory,
        private CompiledCacheManifest  $compiledCacheManifest,
        private Filesystem             $filesystem,
    ) {
    }

    public function requiresRebuild(CompiledCacheName $compiledCacheName, CompiledCacheSources $compiledCacheSources): bool
    {
        return $this->check(compiledCacheName: $compiledCacheName, compiledCacheSources: $compiledCacheSources) !== CompiledCacheFreshness::FRESH;
    }

    public function check(CompiledCacheName $compiledCacheName, CompiledCacheSources $compiledCacheSources): CompiledCacheFreshness
    {
        $resolveCompiledCachePath = new ResolveCompiledCachePath(compiledCacheDirectory: $this->compiledCacheDirectory);
        $compiledCachePath = $resolveCompiledCachePath->resolveArtifactPath(compiledCacheName: $compiledCacheName);

        if (! $this->filesystem->exists($compiledCachePath->toString())) {
            return CompiledCacheFreshness::MISSING;
        }

        if (! $this->compiledCacheManifest->has(name: $compiledCacheName->toString())) {
            return CompiledCacheFreshness::STALE;
        }

        if (! $this->compiledCacheManifest->isFresh(name: $compiledCacheName->toString(), compiledCacheSources: $compiledCacheSources)) {
            return CompiledCacheFreshness::STALE;
        }

        return CompiledCacheFreshness::FRESH;
    }
}
