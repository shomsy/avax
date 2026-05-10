<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\PublicSurface;

use Avax\Components\Application\Filesystem\System\Capabilities\LocalPermissions\ChangePathPermissions;
use Avax\Components\Application\Filesystem\System\Capabilities\LocalPermissions\CheckPathIsReadable;
use Avax\Components\Application\Filesystem\System\Capabilities\LocalPermissions\CheckPathIsWritable;
use Avax\Components\Application\Filesystem\System\Capabilities\LocalPermissions\CheckPathPermissions;
use Avax\Components\Application\Filesystem\System\Flows\AppendToFile\AppendToFile;
use Avax\Components\Application\Filesystem\System\Flows\CheckPathExists\CheckPathExists;
use Avax\Components\Application\Filesystem\System\Flows\CheckPathIsDirectory\CheckPathIsDirectory;
use Avax\Components\Application\Filesystem\System\Flows\CheckPathIsFile\CheckPathIsFile;
use Avax\Components\Application\Filesystem\System\Flows\ClearDirectory\ClearDirectory;
use Avax\Components\Application\Filesystem\System\Flows\CopyFile\CopyFile;
use Avax\Components\Application\Filesystem\System\Flows\CreateDirectory\CreateDirectory;
use Avax\Components\Application\Filesystem\System\Flows\DeleteDirectory\DeleteDirectory;
use Avax\Components\Application\Filesystem\System\Flows\DeleteFile\DeleteFile;
use Avax\Components\Application\Filesystem\System\Flows\ListDirectory\ListDirectory;
use Avax\Components\Application\Filesystem\System\Flows\ListFilesByPattern\ListFilesByPattern;
use Avax\Components\Application\Filesystem\System\Flows\MoveFile\MoveFile;
use Avax\Components\Application\Filesystem\System\Flows\ReadFile\ReadFile;
use Avax\Components\Application\Filesystem\System\Flows\WriteFile\WriteFile;

/**
 * Filesystem public surface — thin facade that delegates to flows.
 *
 * No private state. Each method instantiates its flow on demand.
 */
final class Filesystem
{
    public function read(string $path): string
    {
        return (new ReadFile())->execute($path);
    }

    public function write(string $path, string $content): bool
    {
        return (new WriteFile())->execute($path, $content);
    }

    public function append(string $path, string $content): bool
    {
        return (new AppendToFile())->execute($path, $content);
    }

    public function copy(string $source, string $destination): bool
    {
        return (new CopyFile())->execute($source, $destination);
    }

    public function move(string $source, string $destination): bool
    {
        return (new MoveFile())->execute($source, $destination);
    }

    public function delete(string $path): bool
    {
        return (new DeleteFile())->execute($path);
    }

    public function exists(string $path): bool
    {
        return (new CheckPathExists())->execute($path);
    }

    public function createDirectory(string $path, int $permissions = 0o755): bool
    {
        return (new CreateDirectory())->execute($path, $permissions);
    }

    public function deleteDirectory(string $path): bool
    {
        return (new DeleteDirectory())->execute($path);
    }

    public function clearDirectory(string $path): bool
    {
        return (new ClearDirectory())->execute($path);
    }

    /**
     * @return list<string>
     */
    public function listDirectory(string $path): array
    {
        return (new ListDirectory())->execute($path);
    }

    /**
     * @return list<string>
     */
    public function listFilesByPattern(string $pattern) : array
    {
        return (new ListFilesByPattern())->execute($pattern);
    }

    public function isFile(string $path) : bool
    {
        return (new CheckPathIsFile())->execute($path);
    }

    public function isDirectory(string $path) : bool
    {
        return (new CheckPathIsDirectory())->execute($path);
    }

    public function isReadable(string $path): bool
    {
        return (new CheckPathIsReadable())->execute($path);
    }

    public function isWritable(string $path): bool
    {
        return (new CheckPathIsWritable())->execute($path);
    }

    public function permissions(string $path): ?int
    {
        return (new CheckPathPermissions())->execute($path);
    }

    public function changePermissions(string $path, int $permissions): bool
    {
        return (new ChangePathPermissions())->execute($path, $permissions);
    }
}
