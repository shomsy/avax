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
        private CompiledCacheDirectory $directory,
        private CompiledCacheManifest  $manifest
    ) {}

    public function requiresRebuild(CompiledCacheName $name, CompiledCacheSources $sources) : bool
    {
        return $this->check(name: $name, sources: $sources) !== CompiledCacheFreshness::FRESH;
    }

    public function check(CompiledCacheName $name, CompiledCacheSources $sources) : CompiledCacheFreshness
    {
        $pathResolver = new ResolveCompiledCachePath(directory: $this->directory);
        $artifactPath = $pathResolver->resolveArtifactPath(name: $name);

        if (! file_exists($artifactPath->toString())) {
            return CompiledCacheFreshness::MISSING;
        }

        if (! $this->manifest->has(name: $name->toString())) {
            return CompiledCacheFreshness::STALE;
        }

        if (! $this->manifest->isFresh(name: $name->toString(), sources: $sources)) {
            return CompiledCacheFreshness::STALE;
        }

        return CompiledCacheFreshness::FRESH;
    }
}