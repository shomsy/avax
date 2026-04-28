<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

final readonly class ResolveCompiledCachePath
{
    public function __construct(
        private CompiledCacheDirectory $directory
    ) {}

    public function resolveManifestPath() : CompiledCachePath
    {
        return $this->directory->resolveManifestPath();
    }

    public function resolveWithinBase(string $artifactName) : CompiledCachePath
    {
        return $this->resolveArtifactPath(name: new CompiledCacheName(name: $artifactName));
    }

    public function resolveArtifactPath(CompiledCacheName $name) : CompiledCachePath
    {
        return $this->directory->resolve(filename: $name->toString());
    }
}