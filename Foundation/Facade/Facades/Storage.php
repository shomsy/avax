<?php

declare(strict_types=1);

namespace Avax\Facade\Facades;

use Avax\Facade\BaseFacade;
use Avax\Filesystem\Storage\FileStorageInterface;

/**
 * Facade for accessing the Filesystem service.
 *
 * @method static string read(string $path)
 * @method static bool write(string $path, string $content, bool $append = false)
 * @method static bool delete(string $path)
 * @method static bool exists(string $path)
 * @method static bool createDirectory(string $directory)
 * @method static bool deleteDirectory(string $directory)
 * @method static bool setPermissions(string $path, int $permissions)
 * @method static FileStorageInterface disk(?string $name = null)
 * @method static bool isWritable(string $path)
 * @method static bool clear(string $directory)
 * @method static bool hasPermission(string $path, int $permissions)
 */
class Storage extends BaseFacade
{
    /**
     * The service key used to resolve the Filesystem service from the container.
     */
    protected static string $accessor = 'Storage';
}
