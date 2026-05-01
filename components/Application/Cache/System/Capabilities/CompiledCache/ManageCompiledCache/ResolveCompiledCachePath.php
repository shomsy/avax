<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

final readonly class ResolveCompiledCachePath
{
    public function __construct(
        CompiledCacheDirectory $directory,
    )
    {
        $this->compiledCacheDirectory = $directory;
    }

    private CompiledCacheDirectory $compiledCacheDirectory;

    public function resolveManifestPath() : CompiledCachePath
    {
        return $this->compiledCacheDirectory->resolveManifestPath();
    }

    public function resolveWithinBase(string $artifactName) : CompiledCachePath
    {
        return $this->resolveArtifactPath(name: new CompiledCacheName(name: $artifactName));
    }

    public function resolveArtifactPath(CompiledCacheName $name) : CompiledCachePath
    {
        return $this->compiledCacheDirectory->resolve(filename: $name->toString());
    }
}
