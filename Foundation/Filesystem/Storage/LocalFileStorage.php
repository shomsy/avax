<?php

declare(strict_types=1);

namespace Avax\Filesystem\Storage;

use FilesystemIterator;
use Avax\Filesystem\Exceptions\DirectoryCreationException;
use Avax\Filesystem\Exceptions\DirectoryDeletionException;
use Avax\Filesystem\Exceptions\FileDeleteException;
use RuntimeException;

/**
 * Class LocalFileStorage
 *
 * Provides local file storage operations, including reading, writing, and managing directories.
 */
class LocalFileStorage implements FileStorageInterface
{
    /**
     * Reads the content of a file.
     *
     * @param string $path Path to the file.
     *
     * @return string The file contents.
     * @throws FileNotFoundException If the file does not exist or cannot be read.
     */
    public function read(string $path) : string
    {
        if (! file_exists($path)) {
            throw new FileNotFoundException(string: sprintf('File not found: %s', $path));
        }

        if (! is_readable($path)) {
            throw new FileNotFoundException(string: sprintf('File is not readable: %s', $path));
        }

        $content = file_get_contents($path);
        if ($content === false) {
            throw new RuntimeException(message: sprintf('Failed to read file: %s', $path));
        }

        return $content;
    }

    /**
     * Writes content to a file, creating directories if necessary.
     *
     * @param string $path    Path to the file.
     * @param string $content Content to write.
     *
     * @return bool True on success.
     * @throws FileWriteException If writing fails.
     */
    public function write(string $path, string $content, bool $append = false) : bool
    {
        $directory = dirname($path);
        if (! is_dir($directory) && ! $this->createDirectory(directory: $directory)) {
            throw new FileWriteException(string: sprintf('Failed to create directory: %s', $directory));
        }

        $flags = $append ? FILE_APPEND | LOCK_EX : 0;
        if (file_put_contents($path, $content . PHP_EOL, $flags) === false) {
            throw new FileWriteException(string: "Failed to write to file: {$path}");
        }

        return true;
    }

    /**
     * Creates a directory with specified permissions.
     *
     * @param string $directory   Path to the directory.
     * @param int    $permissions Permissions to set (default: 0755).
     *
     * @return bool True on success.
     * @throws DirectoryCreationException If creation fails.
     */
    public function createDirectory(string $directory, int $permissions = 0755) : bool
    {
        if (is_dir($directory)) {
            return true; // Directory already exists.
        }

        if (! mkdir($directory, $permissions, true) && ! is_dir($directory)) {
            throw new DirectoryCreationException(message: sprintf('Failed to create directory: %s', $directory));
        }

        return true;
    }

    /**
     * Checks if a file or directory exists.
     *
     * @param string $path Path to check.
     *
     * @return bool True if it exists, false otherwise.
     */
    public function exists(string $path) : bool
    {
        return file_exists($path);
    }

    /**
     * Deletes a directory and its contents.
     *
     * @param string $directory Path to the directory.
     *
     * @return bool True on success.
     * @throws DirectoryDeletionException If deletion fails.
     */
    public function deleteDirectory(string $directory) : bool
    {
        if (! is_dir($directory)) {
            return true; // Non-existent directories are considered "deleted".
        }

        $this->clear(directory: $directory);

        if (! rmdir($directory)) {
            throw new DirectoryDeletionException(message: sprintf('Failed to delete directory: %s', $directory));
        }

        return true;
    }

    /**
     * Clears the contents of a directory.
     *
     * @param string $directory Path to the directory.
     *
     * @return bool True on success.
     * @throws RuntimeException If unable to clear the directory.
     */
    public function clear(string $directory) : bool
    {
        if (! is_dir($directory)) {
            throw new RuntimeException(message: sprintf('Not a directory: %s', $directory));
        }

        foreach (new FilesystemIterator(directory: $directory, flags: FilesystemIterator::SKIP_DOTS) as $item) {
            $itemPath = $item->getPathname();

            if ($item->isDir()) {
                $this->deleteDirectory(directory: $itemPath);
            } else {
                $this->delete(path: $itemPath);
            }
        }

        return true;
    }

    /**
     * Deletes a file.
     *
     * @param string $path Path to the file.
     *
     * @return bool True on success.
     * @throws FileDeleteException If deletion fails.
     */
    public function delete(string $path) : bool
    {
        if (! file_exists($path)) {
            return true; // Consider non-existent files as "deleted".
        }

        if (! unlink($path)) {
            throw new FileDeleteException(message: sprintf('Failed to delete file: %s', $path));
        }

        return true;
    }

    /**
     * Checks if a path is writable.
     *
     * @param string $path Path to check.
     *
     * @return bool True if writable, false otherwise.
     */
    public function isWritable(string $path) : bool
    {
        return is_writable($path);
    }

    /**
     * Sets permissions for a file or directory.
     *
     * @param string $path        Path to the file or directory.
     * @param int    $permissions Permissions to set.
     *
     * @return bool True on success.
     * @throws RuntimeException If chmod fails.
     */
    public function setPermissions(string $path, int $permissions) : bool
    {
        if (! file_exists($path)) {
            throw new RuntimeException(message: sprintf('Path does not exist: %s', $path));
        }

        if (! @chmod($path, $permissions)) { // Suppress warning to handle it manually
            error_log(sprintf('Failed to set permissions on: %s', $path));

            return false;
        }

        return true;
    }


    /**
     * Checks if the given path has the specified permissions.
     *
     * @param string $path        The file or directory path to check permissions for.
     * @param int    $permissions The permissions to check against.
     *
     * @return bool True if the path has the specified permissions, false otherwise.
     */
    public function hasPermission(string $path, int $permissions) : bool
    {
        if (! file_exists($path)) {
            return false; // Path does not exist, so it cannot have the specified permissions.
        }

        $actualPermissions = fileperms($path) & 0777; // Get permissions and mask to relevant bits.

        return $actualPermissions === $permissions;
    }
}
