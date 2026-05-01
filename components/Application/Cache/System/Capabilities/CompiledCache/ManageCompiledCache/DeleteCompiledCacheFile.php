<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

final readonly class DeleteCompiledCacheFile
{
    private CompiledCacheDirectory $compiledCacheDirectory;

    public function __construct(
        CompiledCacheDirectory $directory,
    )
    {
        $this->compiledCacheDirectory = $directory;
    }

    public function delete(CompiledCacheName $name) : void
    {
        $resolveCompiledCachePath = new ResolveCompiledCachePath(directory: $this->compiledCacheDirectory);
        $compiledCachePath = $resolveCompiledCachePath->resolveArtifactPath(name: $name);

        if (file_exists($compiledCachePath->toString())) {
            unlink($compiledCachePath->toString());
        }
    }

    public function deleteIfExists(CompiledCacheName $name) : bool
    {
        $resolveCompiledCachePath = new ResolveCompiledCachePath(directory: $this->compiledCacheDirectory);
        $compiledCachePath = $resolveCompiledCachePath->resolveArtifactPath(name: $name);

        if (! file_exists($compiledCachePath->toString())) {
            return false;
        }

        return unlink($compiledCachePath->toString());
    }
}
