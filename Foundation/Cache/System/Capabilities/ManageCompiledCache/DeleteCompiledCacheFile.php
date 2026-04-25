<?php

declare(strict_types=1);

namespace Avax\Cache\System\Capabilities\ManageCompiledCache;

final class DeleteCompiledCacheFile
{
    public function __construct(
        private CompiledCacheDirectory $directory
    ) {}

    public function delete(CompiledCacheName $name) : void
    {
        $pathResolver = new ResolveCompiledCachePath($this->directory);
        $path         = $pathResolver->resolveArtifactPath($name);

        if (file_exists($path->toString())) {
            unlink($path->toString());
        }
    }

    public function deleteIfExists(CompiledCacheName $name) : bool
    {
        $pathResolver = new ResolveCompiledCachePath($this->directory);
        $path         = $pathResolver->resolveArtifactPath($name);

        if (! file_exists($path->toString())) {
            return false;
        }

        return unlink($path->toString());
    }
}