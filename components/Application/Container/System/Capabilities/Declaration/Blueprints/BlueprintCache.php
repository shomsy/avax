<?php

declare(strict_types=1);

namespace Avax\Components\Application\Container\System\Capabilities\Declaration\Blueprints;

use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Errors\ContainerException;
use Avax\Components\Application\Container\System\Capabilities\Diagnostics\Observability\ResolutionMetrics;

/**
 * Caches service blueprints in memory and optional disk artifacts.
 */
final class BlueprintCache
{
    /** @var array<string, ServiceBlueprint> */
    private array                           $items = [];
    private readonly ResolutionMetrics|null $metrics;
    private readonly bool                   $debug;
    private readonly string                 $cacheVersion;
    private readonly string                 $cacheDir;

    public function __construct(
        string|null            $cacheDir = null,
        string|null            $cacheVersion = null,
        bool|null              $debug = null,
        ResolutionMetrics|null $metrics = null
    )
    {
        $cacheDir           ??= '';
        $cacheVersion       ??= 'container-v1';
        $debug              ??= false;
        $this->cacheDir     = $cacheDir;
        $this->cacheVersion = $cacheVersion;
        $this->debug        = $debug;
        $this->metrics      = $metrics;
    }

    /**
     * Returns whether source freshness should be rechecked on reads.
     */
    public function shouldValidateSource() : bool
    {
        return $this->debug;
    }

    /**
     * Reads one blueprint from memory or disk cache.
     */
    public function get(string $class, string $fingerprint = '') : ServiceBlueprint|null
    {
        $cached = $this->items[$class] ?? null;
        if ($cached instanceof ServiceBlueprint) {
            if (! $this->debug || $fingerprint === '' || $cached->fingerprint === $fingerprint) {
                $this->metrics?->increment(name: 'container_blueprint_cache_memory_hits_total');

                return $cached;
            }

            unset($this->items[$class]);
        }

        if (! $this->isEnabled()) {
            $this->metrics?->increment(name: 'container_blueprint_cache_misses_total');

            return null;
        }

        $path = $this->pathFor(class: $class);
        if (! is_file(filename: $path)) {
            $this->metrics?->increment(name: 'container_blueprint_cache_misses_total');

            return null;
        }

        $loaded = require $path;
        if (! $loaded instanceof ServiceBlueprint) {
            $this->metrics?->increment(name: 'container_blueprint_cache_misses_total');

            return null;
        }

        if ($this->debug && $fingerprint !== '' && $loaded->fingerprint !== $fingerprint) {
            $this->forget(class: $class);
            $this->metrics?->increment(name: 'container_blueprint_cache_misses_total');

            return null;
        }

        $this->items[$class] = $loaded;
        $this->metrics?->increment(name: 'container_blueprint_cache_disk_hits_total');

        return $loaded;
    }

    private function isEnabled() : bool
    {
        return $this->cacheDir !== '';
    }

    private function pathFor(string $class) : string
    {
        return $this->directory() . '/' . sha1(string: $class) . '.php';
    }

    private function directory() : string
    {
        return rtrim(string: $this->cacheDir, characters: '/\\') . '/container/' . rawurlencode(string: $this->cacheVersion) . '/blueprints';
    }

    /**
     * Removes one blueprint from memory and disk cache.
     */
    public function forget(string $class) : void
    {
        unset($this->items[$class]);

        $path = $this->pathFor(class: $class);
        if (is_file(filename: $path)) {
            unlink(filename: $path);
        }
    }

    /**
     * Stores one blueprint in memory and optional disk cache.
     *
     * @throws ContainerException
     */
    public function put(ServiceBlueprint $blueprint) : ServiceBlueprint
    {
        $this->items[$blueprint->class] = $blueprint;
        $this->metrics?->increment(name: 'container_blueprint_compiles_total');

        if (! $this->isEnabled()) {
            return $blueprint;
        }

        $directory = dirname(path: $this->pathFor(class: $blueprint->class));
        if (! is_dir(filename: $directory) && ! mkdir(directory: $directory, permissions: 0777, recursive: true) && ! is_dir(filename: $directory)) {
            throw new ContainerException(message: "Cannot create blueprint cache directory [{$directory}].");
        }

        $path = $this->pathFor(class: $blueprint->class);
        $temp = $path . '.' . uniqid(prefix: 'tmp', more_entropy: true);
        $body = '<?php' . PHP_EOL . PHP_EOL . 'return ' . var_export(value: $blueprint, return: true) . ';' . PHP_EOL;

        if (file_put_contents(filename: $temp, data: $body, flags: LOCK_EX) === false) {
            throw new ContainerException(message: "Cannot write blueprint cache file [{$temp}].");
        }

        if (! rename(from: $temp, to: $path)) {
            unlink(filename: $temp);
            throw new ContainerException(message: "Cannot publish blueprint cache file [{$path}].");
        }

        return $blueprint;
    }

    /**
     * Clears the full blueprint cache.
     */
    public function flush() : void
    {
        $this->items = [];

        $directory = $this->directory();
        if (! is_dir(filename: $directory)) {
            return;
        }

        $files = scandir(directory: $directory);
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $directory . '/' . $file;
            if (is_dir(filename: $path)) {
                $this->deleteDirectory(directory: $path);
                continue;
            }

            unlink(filename: $path);
        }
    }

    private function deleteDirectory(string $directory) : void
    {
        $files = scandir(directory: $directory);
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $directory . '/' . $file;
            if (is_dir(filename: $path)) {
                $this->deleteDirectory(directory: $path);
                continue;
            }

            unlink(filename: $path);
        }

        rmdir(directory: $directory);
    }
}
