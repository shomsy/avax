<?php

declare(strict_types=1);

namespace Avax\Container\DI\Capabilities\Declaration\Blueprints;

use Avax\Container\DI\Capabilities\Diagnostics\Errors\ContainerException;
use Avax\Container\DI\Capabilities\Diagnostics\Observability\ResolutionMetrics;

/**
 * Caches service blueprints in memory and optional disk artifacts.
 */
final class BlueprintCache
{
    /** @var array<string, ServiceBlueprint> */
    private array $items = [];

    public function __construct(
        private readonly string                 $cacheDir = '',
        private readonly string                 $cacheVersion = 'container-v1',
        private readonly bool                   $debug = false,
        private readonly ResolutionMetrics|null $metrics = null
    ) {}

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
        if (! is_file($path)) {
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
        return $this->directory() . '/' . sha1($class) . '.php';
    }

    private function directory() : string
    {
        return rtrim($this->cacheDir, '/\\') . '/container/' . rawurlencode($this->cacheVersion) . '/blueprints';
    }

    /**
     * Removes one blueprint from memory and disk cache.
     */
    public function forget(string $class) : void
    {
        unset($this->items[$class]);

        $path = $this->pathFor(class: $class);
        if (is_file($path)) {
            unlink($path);
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

        $directory = dirname($this->pathFor(class: $blueprint->class));
        if (! is_dir($directory) && ! mkdir($directory, 0777, true) && ! is_dir($directory)) {
            throw new ContainerException(message: "Cannot create blueprint cache directory [{$directory}].");
        }

        $path = $this->pathFor(class: $blueprint->class);
        $temp = $path . '.' . uniqid('tmp', true);
        $body = '<?php' . PHP_EOL . PHP_EOL . 'return ' . var_export($blueprint, true) . ';' . PHP_EOL;

        if (file_put_contents($temp, $body, LOCK_EX) === false) {
            throw new ContainerException(message: "Cannot write blueprint cache file [{$temp}].");
        }

        if (! rename($temp, $path)) {
            @unlink($temp);
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
        if (! is_dir($directory)) {
            return;
        }

        $files = scandir($directory);
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $directory . '/' . $file;
            if (is_dir($path)) {
                $this->deleteDirectory(directory: $path);
                continue;
            }

            unlink($path);
        }
    }

    private function deleteDirectory(string $directory) : void
    {
        $files = scandir($directory);
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if ($file === '.' || $file === '..') {
                continue;
            }

            $path = $directory . '/' . $file;
            if (is_dir($path)) {
                $this->deleteDirectory(directory: $path);
                continue;
            }

            unlink($path);
        }

        rmdir($directory);
    }
}
