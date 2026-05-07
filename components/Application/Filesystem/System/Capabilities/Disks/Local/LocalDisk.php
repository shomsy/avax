<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Capabilities\Disks\Local;

use Avax\Components\Application\Filesystem\System\Capabilities\Disks\Disk;
use FilesystemIterator;
use Override;
use RuntimeException;
use SplFileInfo;
use Throwable;

final readonly class LocalDisk implements Disk
{
    private string $root;

    public function __construct(?string $root = null)
    {
        // Use current working directory if no root is provided, but typically a root is expected
        $this->root = (string) ($root ? (realpath($root) ?: $root) : getcwd());
    }

    #[Override]
    public function read(string $path): string
    {
        $safePath = $this->resolveSafePath($path);

        if (! file_exists($safePath) || ! is_readable($safePath)) {
            throw new RuntimeException('File not found or not readable: '.$path);
        }

        $content = file_get_contents($safePath);
        if ($content === false) {
            throw new RuntimeException('Failed to read file: '.$path);
        }

        return $content;
    }

    #[Override]
    public function write(string $path, string $content, bool $append = false): bool
    {
        $safePath  = $this->resolveSafePath($path);
        $directory = dirname($safePath);
        if (! is_dir($directory)) {
            mkdir($directory, 0o755, true);
        }

        $flags = $append ? FILE_APPEND | LOCK_EX : 0;

        return file_put_contents($safePath, $content, $flags) !== false;
    }

    #[Override]
    public function copy(string $source, string $destination): bool
    {
        $safeSource      = $this->resolveSafePath($source);
        $safeDestination = $this->resolveSafePath($destination);

        if (! file_exists($safeSource)) {
            throw new RuntimeException('Source file not found: '.$source);
        }

        $directory = dirname($safeDestination);
        if (! is_dir($directory)) {
            mkdir($directory, 0o755, true);
        }

        return copy($safeSource, $safeDestination);
    }

    #[Override]
    public function move(string $source, string $destination): bool
    {
        $safeSource      = $this->resolveSafePath($source);
        $safeDestination = $this->resolveSafePath($destination);

        if (! file_exists($safeSource)) {
            throw new RuntimeException('Source file not found: '.$source);
        }

        $directory = dirname($safeDestination);
        if (! is_dir($directory)) {
            mkdir($directory, 0o755, true);
        }

        return rename($safeSource, $safeDestination);
    }

    #[Override]
    public function delete(string $path): bool
    {
        $safePath = $this->resolveSafePath($path);

        return file_exists($safePath) ? unlink($safePath) : true;
    }

    #[Override]
    public function exists(string $path): bool
    {
        return file_exists($this->resolveSafePath($path));
    }

    #[Override]
    public function lastModified(string $path): ?int
    {
        $safePath = $this->resolveSafePath($path);

        return file_exists($safePath) ? (filemtime($safePath) ?: null) : null;
    }

    #[Override]
    public function createDirectory(string $path, int $permissions = 0o755): bool
    {
        $safePath = $this->resolveSafePath($path);

        return is_dir($safePath) || mkdir($safePath, $permissions, true);
    }

    #[Override]
    public function deleteDirectory(string $path): bool
    {
        $safePath = $this->resolveSafePath($path);

        if (! is_dir($safePath)) {
            return true;
        }

        $this->clear($path);

        return rmdir($safePath);
    }

    #[Override]
    public function clear(string $path): bool
    {
        $safePath = $this->resolveSafePath($path);

        if (! is_dir($safePath)) {
            return false;
        }

        foreach (new FilesystemIterator($safePath, FilesystemIterator::SKIP_DOTS) as $item) {
            $itemPath = $item->getPathname();
            // We need to convert absolute path back to relative or pass absolute safely
            $relativePath = $this->getRelativePath($itemPath);
            $item->isDir() ? $this->deleteDirectory($relativePath) : $this->delete($relativePath);
        }

        return true;
    }

    #[Override]
    public function isWritable(string $path): bool
    {
        return is_writable($this->resolveSafePath($path));
    }

    #[Override]
    public function setPermissions(string $path, int $permissions): bool
    {
        $safePath = $this->resolveSafePath($path);

        return file_exists($safePath) && chmod($safePath, $permissions);
    }

    #[Override]
    public function listFiles(string $path): array
    {
        $safePath = $this->resolveSafePath($path);

        if (! is_dir($safePath) || ! is_readable($safePath)) {
            return [];
        }

        try {
            $iterator = new FilesystemIterator($safePath, FilesystemIterator::SKIP_DOTS);

            return array_map(
                fn (SplFileInfo $file) : string => $this->getRelativePath($file->getPathname()),
                iterator_to_array($iterator, false),
            );
        } catch (Throwable) {
            return [];
        }
    }

    private function resolveSafePath(string $path) : string
    {
        // 1. Remove any null bytes
        $path = str_replace("\0", '', $path);

        // 2. Handle absolute paths if they start with the root
        if (str_starts_with($path, $this->root)) {
            $realPath = realpath($path);
        } else {
            // 3. Prefix with root
            $fullPath = $this->root . DIRECTORY_SEPARATOR . ltrim($path, DIRECTORY_SEPARATOR);
            // 4. Resolve dots and symlinks
            $realPath = realpath($fullPath);

            // If realpath failed (e.g. file doesn't exist), we need a manual dot-resolver for the directory part
            if ($realPath === false) {
                $realPath = $this->manualResolve($fullPath);
            }
        }

        // 5. Ensure the resolved path is still within the root
        if ($realPath === false || ! str_starts_with($realPath, $this->root)) {
            throw new RuntimeException('Path traversal attempt detected: ' . $path);
        }

        return $realPath;
    }

    private function getRelativePath(string $absolutePath) : string
    {
        return ltrim(str_replace($this->root, '', $absolutePath), DIRECTORY_SEPARATOR);
    }

    private function manualResolve(string $path) : string
    {
        $parts = array_filter(explode(DIRECTORY_SEPARATOR, $path), fn (string $s) : bool => strlen($s) > 0);
        $absolutes = [];
        foreach ($parts as $part) {
            if ('.' === $part) continue;
            if ('..' === $part) {
                array_pop($absolutes);
            } else {
                $absolutes[] = $part;
            }
        }
        $resolved = (DIRECTORY_SEPARATOR === '/' ? '/' : '') . implode(DIRECTORY_SEPARATOR, $absolutes);

        return $resolved;
    }
}
