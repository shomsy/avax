<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Routing;

use Avax\Components\Application\Filesystem\System\PublicSurface\Filesystem;
use Avax\Framework\System\Capabilities\Routing\Types\RouteCacheFailed;

/**
 * LoadCachedRoutes — Loads and validates cached route table.
 */
final readonly class LoadCachedRoutes
{
    public function __construct(
        private Filesystem $filesystem,
        private string     $cacheDirectory = '',
    ) {
    }

    /**
     * @return array<string, mixed>
     *
     * @throws RouteCacheFailed When cache file is not readable, fails to load, or is not an array
     */
    public function load(): array
    {
        $cacheFile = $this->resolveCacheFile();

        if (! $this->filesystem->isReadable($cacheFile)) {
            throw new RouteCacheFailed('Route cache file not found. Run: php avax route:cache');
        }

        /** @var mixed $routes */
        $routes = @include $cacheFile;

        if ($routes === false) {
            throw new RouteCacheFailed('Failed to load route cache file');
        }

        if (!is_array($routes)) {
            throw new RouteCacheFailed('Route cache file must return an array');
        }

        return $routes;
    }

    public function exists(): bool
    {
        return $this->filesystem->isReadable($this->resolveCacheFile());
    }

    public function clear(): bool
    {
        $cacheFile = $this->resolveCacheFile();
        $metaFile = $cacheFile.'.meta';

        $deleted = true;

        if ($this->filesystem->isReadable($cacheFile)) {
            $deleted = $this->filesystem->delete($cacheFile);
        }

        if ($this->filesystem->isReadable($metaFile)) {
            $deleted = $deleted && $this->filesystem->delete($metaFile);
        }

        return $deleted;
    }

    private function resolveCacheFile(): string
    {
        $dir = $this->cacheDirectory !== ''
            ? $this->cacheDirectory
            : dirname(__DIR__, 4).'/storage/framework/cache/routes';

        return $dir.'/routes.php';
    }
}
