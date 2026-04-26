<?php

declare(strict_types=1);

namespace Avax\Facade\Facades;

use Avax\Facade\BaseFacade;
use Avax\Filesystem\Disks\Disk;

/**
 * @deprecated Facades hide dependencies. Prefer explicit DI injection of FilesystemInterface.
 *
 * Facade for accessing the Filesystem service.
 *
 * @method static string get(string $path)
 * @method static void put(string $path, string $content)
 * @method static void append(string $path, string $content)
 * @method static void copy(string $source, string $destination)
 * @method static void move(string $source, string $destination)
 * @method static bool exists(string $path)
 * @method static void delete(string $path)
 * @method static int|null lastModified(string $path)
 * @method static void ensureDirectory(string $path)
 * @method static void createDirectory(string $path, int $permissions = 0755)
 * @method static void deleteDirectory(string $path)
 * @method static void clearDirectory(string $path)
 * @method static array listFiles(string $path)
 * @method static bool isWritable(string $path)
 * @method static bool setPermissions(string $path, int $permissions)
 * @method static bool hasPermission(string $path, int $permissions)
 * @method static Disk disk(string|null $name = null)
 */
class Storage extends BaseFacade
{
    /**
     * The service key used to resolve the Filesystem service from the container.
     */
    protected static string $accessor = 'Storage';
}
