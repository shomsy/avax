<?php

declare(strict_types=1);

namespace Avax\Filesystem;

/**
 * Implementation of FileServiceInterface for local filesystem operations.
 * This service handles directory and file management tasks described by the interface.
 */
class LocalFileService implements FileServiceInterface
{
    /**
     * Checks if the given path is a directory.
     *
     * @param string $path The path to check.
     *
     * @return bool True if the path is a directory, false otherwise.
     */
    public function isDirectory(string $path) : bool
    {
        return is_dir(filename: $path);
    }

    /**
     * Creates a directory with the specified permissions.
     *
     * @param string $path        The directory path to create.
     * @param int    $permissions The permissions to set for the directory.
     *
     * @return bool True on success, false on failure.
     *
     * Note: The third argument for mkdir() is set to true to ensure recursive directory creation.
     */
    public function createDirectory(string $path, int $permissions) : bool
    {
        return ! (! is_dir(filename: $path) && ! @mkdir(directory: $path, permissions: $permissions, recursive: true));
    }


    /**
     * Sets the permissions for the given path.
     *
     * @param string $path        The path to set permissions for.
     * @param int    $permissions The new permissions.
     *
     * @return bool True on success, false on failure.
     */
    public function setPermissions(string $path, int $permissions) : bool
    {
        return chmod(filename: $path, permissions: $permissions);
    }

    /**
     * Checks if the given path is writable.
     *
     * @param string $path The path to check.
     *
     * @return bool True if the path is writable, false otherwise.
     */
    public function isWritable(string $path) : bool
    {
        return is_writable(filename: $path);
    }

    /**
     * Checks if a file exists at the given path.
     *
     * @param string $path The path to check.
     *
     * @return bool True if the file exists, false otherwise.
     */
    public function fileExists(string $path) : bool
    {
        return file_exists(filename: $path);
    }

    /**
     * Creates an empty file at the specified path.
     *
     * @param string $path The file path to create.
     *
     * @return bool True on success, false on failure.
     *
     * Note: Uses touch() to create the file if it does not exist.
     */
    public function createFile(string $path) : bool
    {
        if (! $this->fileExists(path: $path)) {
            return touch(filename: $path);
        }

        return true;
    }

    /**
     * Appends content to a file at the specified path.
     *
     * @param string $path    The file path to append content to.
     * @param string $content The content to append.
     *
     * @return bool True on success, false on failure.
     *
     * Note: Uses FILE_APPEND to add content to the end of the file and LOCK_EX to prevent simultaneous write access.
     */
    public function appendToFile(string $path, string $content) : bool
    {
        // Ensure directory exists
        $directory = dirname(path: $path);
        if (! $this->isDirectory(path: $directory) && ! $this->createDirectory(path: $directory, permissions: 0755)) {
            return false;
        }

        return file_put_contents(filename: $path, data: $content . PHP_EOL, flags: FILE_APPEND | LOCK_EX) !== false;
    }
}
