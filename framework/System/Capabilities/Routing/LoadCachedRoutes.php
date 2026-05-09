<?php

declare(strict_types=1);

namespace Avax\Framework\System\Capabilities\Routing;

use Avax\Framework\System\Capabilities\Routing\Foundation\RouteCacheFailed;

/**
 * LoadCachedRoutes — Loads and validates cached route table.
 */
final readonly class LoadCachedRoutes
{
    public function __construct(
        private string $cacheDirectory = '',
    ) {
    }

    /**
     * @return array<string, mixed>
     */
    public function load(): array
    {
        $cacheFile = $this->resolveCacheFile();

        if (!is_file($cacheFile)) {
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
        return is_file($this->resolveCacheFile());
    }

    public function clear(): bool
    {
        $cacheFile = $this->resolveCacheFile();
        $metaFile = $cacheFile.'.meta';

        $deleted = true;

        if (is_file($cacheFile)) {
            $deleted = unlink($cacheFile);
        }

        if (is_file($metaFile)) {
            $deleted = $deleted && unlink($metaFile);
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
