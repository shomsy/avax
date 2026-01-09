<?php

declare(strict_types=1);
namespace Avax\Container\Features\Think\Cache;

use Avax\Container\Features\Think\Model\ServicePrototype;
use RuntimeException;
use Throwable;

/**
 * File-based implementation of service prototype cache.
 * Uses atomic writes and var_export() for high-performance deserialization.
 *
 * @see docs_md/Features/Think/Cache/FilePrototypeCache.md#quick-summary
 */
final readonly class FilePrototypeCache implements PrototypeCache
{
    public function __construct(
        private string $directory
    )
    {
        if (! is_dir($this->directory) && ! mkdir($this->directory, 0775, true) && ! is_dir($this->directory)) {
            throw new RuntimeException(message: "Cannot create prototype cache directory: {$this->directory}");
        }
    }

    public function get(string $class) : ServicePrototype|null
    {
        $path = $this->getPath(class: $class);
        if (! is_file($path)) {
            return null;
        }

        try {
            $prototype = require $path;

            return $prototype instanceof ServicePrototype ? $prototype : null;
        } catch (Throwable) {
            return null;
        }
    }

    private function getPath(string $class) : string
    {
        return $this->directory . DIRECTORY_SEPARATOR . str_replace(['\\', '/'], '_', $class) . '.php';
    }

    /**
     * @throws \Random\RandomException
     * @see docs_md/Features/Think/Cache/FilePrototypeCache.md#method-set
     */
    public function set(string $class, ServicePrototype $prototype) : void
    {
        $path    = $this->getPath(class: $class);
        $content = "<?php\n\nreturn " . var_export($prototype, true) . ";\n";

        $tmp = $path . '.tmp.' . bin2hex(random_bytes(8));

        if (file_put_contents($tmp, $content, LOCK_EX) === false) {
            throw new RuntimeException(message: "Failed to write to temporary prototype cache file: {$tmp}");
        }

        if (! @rename($tmp, $path)) {
            @unlink($tmp);
            throw new RuntimeException(message: "Failed to atomically move prototype cache file to: {$path}");
        }
    }

    public function delete(string $class) : bool
    {
        $path = $this->getPath(class: $class);

        return is_file($path) && @unlink($path);
    }

    /**
     * @see docs_md/Features/Think/Cache/FilePrototypeCache.md#method-clear
     */
    public function clear() : void
    {
        foreach (glob($this->directory . '/*.php') as $file) {
            @unlink($file);
        }
    }

    /**
     * @see docs_md/Features/Think/Cache/FilePrototypeCache.md#method-getcachepath
     */
    public function getCachePath() : string
    {
        return $this->directory;
    }

    /**
     * @see docs_md/Features/Think/Cache/FilePrototypeCache.md#method-count
     */
    public function count() : int
    {
        return count(glob($this->directory . '/*.php'));
    }

    /**
     * @see docs_md/Features/Think/Cache/FilePrototypeCache.md#method-prototypeexists
     */
    public function prototypeExists(string $class) : bool
    {
        return $this->has(class: $class);
    }

    /**
     * @see docs_md/Features/Think/Cache/FilePrototypeCache.md#method-has
     */
    public function has(string $class) : bool
    {
        return is_file($this->getPath(class: $class));
    }
}
