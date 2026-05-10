<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;

final class DeleteCompiledCacheFile
{
    private Filesystem $filesystem;

    public function __construct(private CompiledCacheDirectory $compiledCacheDirectory)
    {
        $this->filesystem = new Filesystem();
    }

    public function delete(CompiledCacheName $compiledCacheName): void
    {
        $resolveCompiledCachePath = new ResolveCompiledCachePath(compiledCacheDirectory: $this->compiledCacheDirectory);
        $compiledCachePath = $resolveCompiledCachePath->resolveArtifactPath(compiledCacheName: $compiledCacheName);

        if ($this->filesystem->exists($compiledCachePath->toString())) {
            $this->filesystem->delete($compiledCachePath->toString());
        }
    }

    public function deleteIfExists(CompiledCacheName $compiledCacheName): bool
    {
        $resolveCompiledCachePath = new ResolveCompiledCachePath(compiledCacheDirectory: $this->compiledCacheDirectory);
        $compiledCachePath = $resolveCompiledCachePath->resolveArtifactPath(compiledCacheName: $compiledCacheName);

        if (! $this->filesystem->exists($compiledCachePath->toString())) {
            return false;
        }

        return $this->filesystem->delete($compiledCachePath->toString());
    }
}
