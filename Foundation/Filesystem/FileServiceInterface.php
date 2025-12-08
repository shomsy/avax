<?php

declare(strict_types=1);

namespace Avax\Filesystem;

/**
 * Interface for file services, outlining methods for directory and file operations.
 *
 * This interface defines the common operations for interacting with the filesystem.
 * Implementing classes should provide the actual logic for handling these filesystem tasks.
 */
interface FileServiceInterface
{
    /**
     * Checks if the given path is a directory.
     *
     * @param string $path The path to check.
     *
     * @return bool True if the path is a directory, false otherwise.
     */
    public function isDirectory(string $path) : bool;

    /**
     * Creates a directory at the specified path with given permissions.
     *
     * @param string $path        The path where the directory should be created.
     * @param int    $permissions The permissions to set for the directory.
     *
     * @return bool True if the directory was successfully created, false otherwise.
     */
    public function createDirectory(string $path, int $permissions) : bool;

    /**
     * Sets permissions for the specified path.
     *
     * @param string $path        The path for which to set the permissions.
     * @param int    $permissions The permissions to set.
     *
     * @return bool True if the permissions were successfully set, false otherwise.
     */
    public function setPermissions(string $path, int $permissions) : bool;

    /**
     * Checks if the given path is writable.
     *
     * @param string $path The path to check.
     *
     * @return bool True if the path is writable, false otherwise.
     */
    public function isWritable(string $path) : bool;

    /**
     * Checks if a file exists at the specified path.
     *
     * @param string $path The path to check.
     *
     * @return bool True if the file exists, false otherwise.
     */
    public function fileExists(string $path) : bool;

    /**
     * Creates a new file at the specified path.
     *
     * @param string $path The path where the file should be created.
     *
     * @return bool True if the file was successfully created, false otherwise.
     */
    public function createFile(string $path) : bool;

    /**
     * Appends content to a file at the specified path.
     *
     * @param string $path    The path of the file to which content should be appended.
     * @param string $content The content to append to the file.
     *
     * @return bool True if the content was successfully appended, false otherwise.
     */
    public function appendToFile(string $path, string $content) : bool;
}