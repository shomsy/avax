<?php

declare(strict_types=1);

namespace Avax\Filesystem\Contracts;

/**
 * Filesystem abstraction for clean architecture.
 *
 * Replaces framework-specific Storage facades with proper dependency injection.
 * Enables testability and framework-agnostic file operations.
 */
interface FilesystemInterface
{
    /**
     * Get the contents of a file.
     *
     * @param string $path The file path
     *
     * @return string The file contents
     *
     * @throws \Avax\Contracts\FilesystemException If file cannot be read
     */
    public function get(string $path) : string;

    /**
     * Write contents to a file.
     *
     * @param string $path    The file path
     * @param string $content The content to write
     *
     * @throws \Avax\Contracts\FilesystemException If file cannot be written
     */
    public function put(string $path, string $content) : void;

    /**
     * Check if a file exists.
     *
     * @param string $path The file path
     *
     * @return bool True if file exists and is readable
     */
    public function exists(string $path) : bool;

    /**
     * Delete a file.
     *
     * @param string $path The file path
     *
     * @throws \Avax\Contracts\FilesystemException If file cannot be deleted
     */
    public function delete(string $path) : void;

    /**
     * Get file modification time.
     *
     * @param string $path The file path
     *
     * @return int|null Unix timestamp or null if file doesn't exist
     */
    public function lastModified(string $path) : int|null;

    /**
     * Ensure directory exists, creating it if necessary.
     *
     * @param string $path Directory path
     *
     * @throws \Avax\Contracts\FilesystemException If directory cannot be created
     */
    public function ensureDirectory(string $path) : void;
}