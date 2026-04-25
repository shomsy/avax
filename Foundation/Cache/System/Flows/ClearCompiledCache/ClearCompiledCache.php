<?php

declare(strict_types=1);

namespace Avax\Cache\System\Flows\ClearCompiledCache;

use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheDirectory;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheManifest;
use Avax\Cache\System\Capabilities\ManageCompiledCache\CompiledCacheName;
use Avax\Cache\System\Capabilities\ManageCompiledCache\DeleteCompiledCacheFile;
use Avax\Cache\System\Capabilities\ManageCompiledCache\ResolveCompiledCachePath;

final class ClearCompiledCache
{
    public function __construct(
        private CompiledCacheDirectory $directory,
        private CompiledCacheManifest  $manifest
    ) {}

    public function clear(string $name) : void
    {
        $nameObj = new CompiledCacheName($name);

        $deleter = new DeleteCompiledCacheFile($this->directory);
        $deleter->delete($nameObj);

        $this->manifest->remove($nameObj->toString());

        $pathResolver = new ResolveCompiledCachePath($this->directory);
        $manifestPath = $pathResolver->resolveManifestPath();
        $this->manifest->save($manifestPath->toString());
    }

    public function clearAll() : void
    {
        foreach ($this->manifest->all() as $entry) {
            $deleter = new DeleteCompiledCacheFile($this->directory);
            $deleter->delete($entry->name);
        }

        $this->manifest = CompiledCacheManifest::empty();

        $pathResolver = new ResolveCompiledCachePath($this->directory);
        $manifestPath = $pathResolver->resolveManifestPath();

        if (file_exists($manifestPath->toString())) {
            unlink($manifestPath->toString());
        }
    }
}