<?php

declare(strict_types=1);

namespace Avax\Filesystem;

/**
 * Async companion contract for runtimes with non-blocking I/O.
 */
interface AsyncFilesystemInterface
{
    public function existsAsync(string $path) : mixed;

    public function getAsync(string $path) : mixed;

    public function putAsync(string $path, string $content) : mixed;

    public function appendAsync(string $path, string $content) : mixed;

    public function copyAsync(string $source, string $destination) : mixed;

    public function moveAsync(string $source, string $destination) : mixed;

    public function deleteAsync(string $path) : mixed;

    public function ensureDirectoryAsync(string $path) : mixed;

    public function ensureDirectoryIsWritableAsync(string $path) : mixed;

    public function createDirectoryAsync(string $path, int $permissions = 0755) : mixed;

    public function deleteDirectoryAsync(string $path) : mixed;

    public function clearDirectoryAsync(string $path) : mixed;

    public function listFilesAsync(string $path) : mixed;

    public function supportsAsync() : bool;

    public function getAsyncRuntime() : string|null;
}
