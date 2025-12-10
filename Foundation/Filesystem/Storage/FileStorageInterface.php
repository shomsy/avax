<?php

declare(strict_types=1);

namespace Avax\Filesystem\Storage;

/**
 * Interface for file storage operations.
 *
 * This interface outlines the methods required to interact with a file system,
 * including reading, writing, deleting files, and managing directories. It standardizes
 * file operations to ensure any implementing class provides these capabilities.
 */
interface FileStorageInterface
{
    /**
     * Lists all files within a given directory.
     *
     * @param string $directory
     *
     * @return array<int, string> List of file paths.
     */
    public function listFiles(string $directory) : array;

    /**
     * Reads the content of a file at the specified path.
     *
     * @param string $path The path to the file.
     *
     * @return string The file content.
     */
    public function read(string $path) : string;

    /**
     * Writes content to a file at the specified path.
     *
     * @param string $path    The path to the file.
     * @param string $content The content to write.
     *
     * @return bool True on success, false otherwise.
     */
    public function write(string $path, string $content, bool $append = false) : bool;

    /**
     * Deletes the file at the specified path.
     *
     * @param string $path The path to the file.
     *
     * @return bool True on success, false otherwise.
     */
    public function delete(string $path) : bool;

    /**
     * Checks if a file or directory exists at the specified path.
     *
     * @param string $path The path to the file or directory.
     *
     * @return bool True if exists, false otherwise.
     */
    public function exists(string $path) : bool;

    /**
     * Creates a directory at the specified path.
     *
     * @param string $directory The path to the directory.
     *
     * @return bool True on success, false otherwise.
     */
    public function createDirectory(string $directory) : bool;

    /**
     * Deletes a directory at the specified path.
     *
     * @param string $directory The path to the directory.
     *
     * @return bool True on success, false otherwise.
     */
    public function deleteDirectory(string $directory) : bool;

    /**
     * Sets permissions for a file or directory at the specified path.
     *
     * @param string $path        The path to the file or directory.
     * @param int    $permissions The permissions to set.
     *
     * @return bool True on success, false otherwise.
     */
    public function setPermissions(string $path, int $permissions) : bool;

    /**
     * Checks if the file or directory at the specified path is writable.
     *
     * @param string $path The path to check for write permissions.
     *
     * @return bool True if the path is writable, false otherwise.
     */
    public function isWritable(string $path) : bool;

    /**
     * Checks if the given path has the specified permissions.
     *
     * @param string $path        The file or directory path to check permissions for.
     * @param int    $permissions The permissions to check against.
     *
     * @return bool True if the path has the specified permissions, false otherwise.
     */
    public function hasPermission(string $path, int $permissions) : bool;

    /**
     * Clears the contents of the specified directory.
     *
     * @param string $directory The directory path to clear.
     *
     * @return bool True if the directory was successfully cleared, false otherwise.
     */
    public function clear(string $directory) : bool;
}