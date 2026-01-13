<?php

declare(strict_types=1);

namespace Avax\Container\Features\Think\Cache;

use Avax\Container\Features\Think\Model\ServicePrototype;
use RuntimeException;
use Throwable;

/**
 * High-performance file-based storage for service blueprints.
 *
 * FilePrototypeCache implements the {@see PrototypeCache} interface using
 * the local filesystem. It optimized for speed by exporting blueprints as
 * standard PHP files via `var_export()`, allowing the PHP opcode cache
 * (OPcache) to store them in memory.
 *
 * @see     docs/Features/Think/Cache/FilePrototypeCache.md
 * @see     PrototypeCache The interface this class implements.
 */
final readonly class FilePrototypeCache implements PrototypeCache
{
    /**
     * Initializes the file cache.
     *
     * @param string $directory Absolute path to the cache folder.
     *
     * @throws RuntimeException If the directory cannot be created or accessed.
     */
    public function __construct(
        private string $directory
    )
    {
        if (! is_dir($this->directory) && ! mkdir($this->directory, 0775, true) && ! is_dir($this->directory)) {
            throw new RuntimeException(message: "Cannot create prototype cache directory: {$this->directory}");
        }
    }

    /**
     * Retrieves a blueprint by requiring the PHP file.
     *
     * @param string $class The class name.
     *
     * @see docs/Features/Think/Cache/FilePrototypeCache.md#method-get
     */
    public function get(string $class) : ServicePrototype|null
    {
        $path = $this->getPath(class: $class);
        if (! is_file($path)) {
            return null;
        }

        try {
            /** @noinspection PhpIncludeInspection */
            $prototype = require $path;

            return $prototype instanceof ServicePrototype ? $prototype : null;
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Determine the filesystem path for a class blueprint.
     */
    private function getPath(string $class) : string
    {
        return $this->directory . DIRECTORY_SEPARATOR . str_replace(['\\', '/'], '_', $class) . '.php';
    }

    /**
     * Saves a blueprint using an atomic write-and-rename pattern.
     *
     *
     *
     * @param string                                                $class
     * @param \Avax\Container\Features\Think\Model\ServicePrototype $prototype
     *
     * @throws \Random\RandomException
     * @see docs/Features/Think/Cache/FilePrototypeCache.md#method-set
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

    /**
     * Deletes a blueprint file.
     *
     *
     * @see docs/Features/Think/Cache/FilePrototypeCache.md#method-delete
     */
    public function delete(string $class) : bool
    {
        $path = $this->getPath(class: $class);

        return is_file($path) && @unlink($path);
    }

    /**
     * Deletes all .php files in the cache directory.
     *
     * @see docs/Features/Think/Cache/FilePrototypeCache.md#method-clear
     */
    public function clear() : void
    {
        foreach (glob($this->directory . '/*.php') as $file) {
            @unlink($file);
        }
    }

    /**
     * Return the base cache directory path.
     */
    public function getCachePath() : string
    {
        return $this->directory;
    }

    /**
     * Count the number of .php files in the cache directory.
     *
     * @see docs/Features/Think/Cache/FilePrototypeCache.md#method-count
     */
    public function count() : int
    {
        return count(glob($this->directory . '/*.php'));
    }

    /**
     * Optimized existence check.
     *
     *
     * @see docs/Features/Think/Cache/FilePrototypeCache.md#method-prototypeexists
     */
    public function prototypeExists(string $class) : bool
    {
        return $this->has(class: $class);
    }

    /**
     * Checks for the presence of the physical file on disk.
     *
     *
     * @see docs/Features/Think/Cache/FilePrototypeCache.md#method-has
     */
    public function has(string $class) : bool
    {
        return is_file($this->getPath(class: $class));
    }
}
