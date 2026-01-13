<?php

declare(strict_types=1);

namespace Avax\Filesystem\Contracts;

/**
 * Asynchronous filesystem interface for modern PHP runtimes.
 *
 * Provides async I/O operations for compatibility with Swoole, ReactPHP,
 * and other event-loop based frameworks. Falls back to sync operations
 * when async runtime is not available.
 *
 * All methods return promises/futures that resolve to the same values
 * as their synchronous counterparts.
 */
interface AsyncFilesystemInterface
{
    /**
     * Asynchronously checks if a file exists.
     *
     * @param string $path File path to check
     *
     * @return mixed Promise resolving to bool
     */
    public function existsAsync(string $path) : mixed;

    /**
     * Asynchronously reads file contents.
     *
     * @param string $path File path to read
     *
     * @return mixed Promise resolving to string
     * @throws \Avax\Contracts\FilesystemException
     */
    public function getAsync(string $path) : mixed;

    /**
     * Asynchronously writes content to file.
     *
     * @param string $path    File path to write
     * @param string $content Content to write
     *
     * @return mixed Promise resolving to bool
     * @throws \Avax\Contracts\FilesystemException
     */
    public function putAsync(string $path, string $content) : mixed;

    /**
     * Asynchronously deletes a file.
     *
     * @param string $path File path to delete
     *
     * @return mixed Promise resolving to bool
     * @throws \Avax\Contracts\FilesystemException
     */
    public function deleteAsync(string $path) : mixed;

    /**
     * Asynchronously ensures directory exists.
     *
     * @param string $path Directory path to create
     *
     * @return mixed Promise resolving to bool
     * @throws \Avax\Contracts\FilesystemException
     */
    public function ensureDirectoryAsync(string $path) : mixed;

    /**
     * Checks if async operations are supported in current runtime.
     */
    public function supportsAsync() : bool;

    /**
     * Gets async runtime identifier (e.g., 'swoole', 'reactphp', 'amp').
     */
    public function getAsyncRuntime() : string|null;
}