<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Capabilities\AsyncIO;

/**
 * Capability boundary interface for async filesystem operations.
 *
 * This interface defines the contract for non-blocking filesystem I/O.
 * Concrete implementations will be provided by async runtime adapters
 * (ReactPHP, Amp, Swoole, Workerman) when available.
 *
 * IMPORTANT: This is an interface-only capability boundary. No concrete
 * implementation is provided here. The RunFilesystemIoSynchronously exists
 * as a stopgap that wraps sync calls under this interface, but it should
 * be replaced with a true async adapter when the runtime supports it.
 *
 * All methods return AsyncOperationPromise instances. Callers should
 * use then()/catch() for proper async flow rather than blocking on
 * getResult().
 */
interface AsyncFilesystemInterface
{
    /**
     * Asynchronously read the contents of a file.
     *
     * @param string $path The file path to read
     * @param array<string, mixed> $options Optional flags (e.g., encoding, offset, length)
     *
     * @return AsyncOperationPromise Promise resolving to file contents (string)
     */
    public function asyncRead(string $path, array $options = []): AsyncOperationPromise;

    /**
     * Asynchronously write contents to a file.
     *
     * @param string $path The file path to write
     * @param string $contents The file contents to write
     * @param array<string, mixed> $options Optional flags (e.g., permissions, append mode)
     *
     * @return AsyncOperationPromise Promise resolving to true on success
     */
    public function asyncWrite(string $path, string $contents, array $options = []): AsyncOperationPromise;

    /**
     * Asynchronously check if a file or directory exists.
     *
     * @param string $path The path to check
     *
     * @return AsyncOperationPromise Promise resolving to bool
     */
    public function asyncExists(string $path): AsyncOperationPromise;

    /**
     * Asynchronously delete a file or directory.
     *
     * @param string $path The path to delete
     * @param array<string, mixed> $options Optional flags (e.g., recursive for directories)
     *
     * @return AsyncOperationPromise Promise resolving to true on success
     */
    public function asyncDelete(string $path, array $options = []): AsyncOperationPromise;

    /**
     * Asynchronously list the contents of a directory.
     *
     * @param string $path The directory path to list
     * @param array<string, mixed> $options Optional flags (e.g., recursive, include hidden, filter)
     *
     * @return AsyncOperationPromise Promise resolving to array of file/directory paths
     */
    public function asyncListDirectory(string $path, array $options = []): AsyncOperationPromise;
}
