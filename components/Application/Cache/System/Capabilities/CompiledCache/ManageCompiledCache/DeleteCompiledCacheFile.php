<?php

declare(strict_types=1);

namespace Avax\Components\Application\Cache\System\Capabilities\CompiledCache\ManageCompiledCache;

final readonly class DeleteCompiledCacheFile
{
    public function __construct(private CompiledCacheDirectory $compiledCacheDirectory)
    {
    }

    public function delete(CompiledCacheName $compiledCacheName) : void
    {
        $resolveCompiledCachePath = new ResolveCompiledCachePath(directory: $this->compiledCacheDirectory);
        $compiledCachePath = $resolveCompiledCachePath->resolveArtifactPath(name: $compiledCacheName);

        if (file_exists($compiledCachePath->toString())) {
            unlink($compiledCachePath->toString());
        }
    }

    public function deleteIfExists(CompiledCacheName $compiledCacheName) : bool
    {
        $resolveCompiledCachePath = new ResolveCompiledCachePath(directory: $this->compiledCacheDirectory);
        $compiledCachePath = $resolveCompiledCachePath->resolveArtifactPath(name: $compiledCacheName);

        if (! file_exists($compiledCachePath->toString())) {
            return false;
        }

        return unlink($compiledCachePath->toString());
    }
}
