<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Capabilities\AsyncIO;

use Fiber;
use InvalidArgumentException;
use Override;
use RuntimeException;
use Throwable;

/**
 * STOPGAP ADAPTER: Synchronous filesystem calls wrapped in async interface.
 *
 * ⚠️  WARNING: THIS IS NOT A TRUE ASYNC IMPLEMENTATION ⚠️
 *
 * This adapter implements AsyncFilesystemInterface but performs all I/O
 * synchronously under the hood using PHP Fibers. It exists solely to
 * unblock development and testing until a real async runtime adapter
 * (ReactPHP, Amp, Swoole, Workerman) is available.
 *
 * HOW IT WORKS:
 * - Each operation is executed inside a Fiber
 * - The Fiber runs the blocking sync call
 * - The result is wrapped in a SyncOperationPromise
 * - From the caller's perspective, the interface looks async
 * - Under the hood, the calling thread IS blocked
 *
 * WHEN TO REPLACE:
 * - When integrating with ReactPHP event loop → use ReactFilesystemAdapter
 * - When integrating with Amp concurrency → use AmpFilesystemAdapter
 * - When deploying on Swoole → use SwooleFilesystemAdapter
 * - When deploying on Workerman → use WorkermanFilesystemAdapter
 *
 * WHY FIBERS:
 * PHP 8.1+ Fibers allow us to structure the code as if it were async,
 * making the eventual swap to a real async adapter transparent at the
 * interface level. The Fiber suspension points are where a real async
 * runtime would yield to the event loop.
 */
final class SyncAsyncFilesystemAdapter implements AsyncFilesystemInterface
{
    /**
     * Asynchronously read the contents of a file.
     *
     * NOTE: This is synchronous under the hood. The Fiber executes
     * blocking file_get_contents() immediately.
     */
    #[Override]
    public function asyncRead(string $path, array $options = []) : AsyncOperationPromise
    {
        if ($path === '') {
            return new SyncOperationPromise(
                rejection: new InvalidArgumentException('File path cannot be empty'),
            );
        }

        $offset = $options['offset'] ?? 0;
        $length = $options['length'] ?? null;

        $fiber = new Fiber(static function () use ($path, $offset, $length) : string {
            $contents = file_get_contents($path);

            if ($contents === false) {
                throw new RuntimeException(
                    sprintf('Failed to read file: %s', $path),
                );
            }

            if ($offset > 0 || $length !== null) {
                return substr($contents, $offset, $length);
            }

            return $contents;
        });

        try {
            $fiber->start();
            $result = $fiber->getReturn();

            return new SyncOperationPromise(result: $result);
        } catch (Throwable $throwable) {
            return new SyncOperationPromise(rejection: $throwable);
        }
    }

    /**
     * Asynchronously write contents to a file.
     *
     * NOTE: This is synchronous under the hood. The Fiber executes
     * blocking file_put_contents() immediately.
     */
    #[Override]
    public function asyncWrite(string $path, string $contents, array $options = []) : AsyncOperationPromise
    {
        if ($path === '') {
            return new SyncOperationPromise(
                rejection: new InvalidArgumentException('File path cannot be empty'),
            );
        }

        $flags = $options['flags'] ?? 0;
        $permissions = $options['permissions'] ?? null;

        $fiber = new Fiber(static function () use ($path, $contents, $flags, $permissions) : bool {
            $dir = dirname($path);

            if (! is_dir($dir) && (! mkdir($dir, 0o755, true) && ! is_dir($dir))) {
                throw new RuntimeException(
                    sprintf('Failed to create directory: %s', $dir),
                );
            }

            $result = file_put_contents($path, $contents, $flags);

            if ($result === false) {
                throw new RuntimeException(
                    sprintf('Failed to write file: %s', $path),
                );
            }

            if ($permissions !== null && ! chmod($path, $permissions)) {
                throw new RuntimeException(
                    sprintf('Failed to set permissions on: %s', $path),
                );
            }

            return true;
        });

        try {
            $fiber->start();
            $result = $fiber->getReturn();

            return new SyncOperationPromise(result: $result);
        } catch (Throwable $throwable) {
            return new SyncOperationPromise(rejection: $throwable);
        }
    }

    /**
     * Asynchronously check if a file or directory exists.
     *
     * NOTE: This is synchronous under the hood.
     */
    #[Override]
    public function asyncExists(string $path) : AsyncOperationPromise
    {
        if ($path === '') {
            return new SyncOperationPromise(
                rejection: new InvalidArgumentException('Path cannot be empty'),
            );
        }

        $fiber = new Fiber(static fn () : bool => file_exists($path));

        try {
            $fiber->start();
            $result = $fiber->getReturn();

            return new SyncOperationPromise(result: $result);
        } catch (Throwable $throwable) {
            return new SyncOperationPromise(rejection: $throwable);
        }
    }

    /**
     * Asynchronously delete a file or directory.
     *
     * NOTE: This is synchronous under the hood.
     *
     * @param array<string, mixed> $options If 'recursive' is true, directories are deleted recursively
     */
    #[Override]
    public function asyncDelete(string $path, array $options = []) : AsyncOperationPromise
    {
        if ($path === '') {
            return new SyncOperationPromise(
                rejection: new InvalidArgumentException('Path cannot be empty'),
            );
        }

        $recursive = $options['recursive'] ?? false;

        $fiber = new Fiber(function () use ($path, $recursive) : bool {
            if (is_file($path) || is_link($path)) {
                if (! unlink($path)) {
                    throw new RuntimeException(
                        sprintf('Failed to delete file: %s', $path),
                    );
                }

                return true;
            }

            if (is_dir($path)) {
                if ($recursive) {
                    if (! $this->deleteDirectoryRecursive($path)) {
                        throw new RuntimeException(
                            sprintf('Failed to delete directory recursively: %s', $path),
                        );
                    }

                    return true;
                }

                // Non-recursive: only delete if empty
                if (! rmdir($path)) {
                    throw new RuntimeException(
                        sprintf('Failed to delete empty directory (use recursive=true for non-empty): %s', $path),
                    );
                }

                return true;
            }

            // Path does not exist
            return false;
        });

        try {
            $fiber->start();
            $result = $fiber->getReturn();

            return new SyncOperationPromise(result: $result);
        } catch (Throwable $throwable) {
            return new SyncOperationPromise(rejection: $throwable);
        }
    }

    /**
     * Recursively delete a directory and its contents.
     */
    private function deleteDirectoryRecursive(string $path) : bool
    {
        if (! is_dir($path)) {
            return false;
        }

        $items = array_diff(scandir($path), ['.', '..']);

        foreach ($items as $item) {
            $fullPath = $path . DIRECTORY_SEPARATOR . $item;

            if (is_dir($fullPath)) {
                $this->deleteDirectoryRecursive($fullPath);
            } else {
                unlink($fullPath);
            }
        }

        return rmdir($path);
    }

    /**
     * Asynchronously list the contents of a directory.
     *
     * NOTE: This is synchronous under the hood.
     *
     * @param array<string, mixed> $options Supported: 'recursive' (bool), 'includeHidden' (bool), 'filter' (callable)
     */
    #[Override]
    public function asyncListDirectory(string $path, array $options = []) : AsyncOperationPromise
    {
        if ($path === '') {
            return new SyncOperationPromise(
                rejection: new InvalidArgumentException('Directory path cannot be empty'),
            );
        }

        $recursive = $options['recursive'] ?? false;
        $includeHidden = $options['includeHidden'] ?? false;
        $filter    = $options['filter'] ?? null;

        if ($filter !== null && ! is_callable($filter)) {
            return new SyncOperationPromise(
                rejection: new InvalidArgumentException('Filter option must be callable'),
            );
        }

        $fiber = new Fiber(function () use ($path, $recursive, $includeHidden, $filter) : array {
            if (! is_dir($path)) {
                throw new RuntimeException(
                    sprintf('Path is not a directory: %s', $path),
                );
            }

            $items = $recursive
                ? $this->listDirectoryRecursive($path, $includeHidden)
                : $this->listDirectorySingle($path, $includeHidden);

            if ($filter !== null) {
                return array_values(array_filter($items, $filter));
            }

            return $items;
        });

        try {
            $fiber->start();
            $result = $fiber->getReturn();

            return new SyncOperationPromise(result: $result);
        } catch (Throwable $throwable) {
            return new SyncOperationPromise(rejection: $throwable);
        }
    }

    /**
     * List directory contents recursively.
     *
     * @return list<string>
     */
    private function listDirectoryRecursive(string $path, bool $includeHidden) : array
    {
        $items   = [];
        $entries = array_diff(scandir($path), ['.', '..']);

        foreach ($entries as $entry) {
            if (! $includeHidden && str_starts_with((string) $entry, '.')) {
                continue;
            }

            $fullPath = $path . DIRECTORY_SEPARATOR . $entry;
            $items[]  = $fullPath;

            if (is_dir($fullPath)) {
                $items = [...$items, ...$this->listDirectoryRecursive($fullPath, $includeHidden)];
            }
        }

        return $items;
    }

    /**
     * List directory contents (non-recursive).
     *
     * @return list<string>
     */
    private function listDirectorySingle(string $path, bool $includeHidden) : array
    {
        $items  = [];
        $handle = opendir($path);

        if ($handle === false) {
            return $items;
        }

        while ( ($entry = readdir($handle)) !== false ) {
            if ($entry === '.') {
                continue;
            }

            if ($entry === '..') {
                continue;
            }

            if (! $includeHidden && str_starts_with($entry, '.')) {
                continue;
            }

            $items[] = $path . DIRECTORY_SEPARATOR . $entry;
        }

        closedir($handle);

        return $items;
    }
}

/**
 * Internal promise implementation for the SyncAsyncFilesystemAdapter.
 *
 * This is a private, immediately-resolved/rejected promise. It exists
 * ONLY to fulfill the AsyncOperationPromise contract for the sync adapter.
 *
 * DO NOT use this class outside of SyncAsyncFilesystemAdapter. When a real
 * async runtime adapter is implemented, it should provide its own promise
 * class that integrates with the event loop or fiber scheduler.
 *
 * @internal
 */
final class SyncOperationPromise implements AsyncOperationPromise
{
    private bool $isResolved;

    private bool $isRejected;

    private mixed $result = null;

    private Throwable|null $throwable = null;

    /**
     * @param mixed $result The resolved value (if resolved)
     * @param Throwable|null $throwable The rejection exception (if rejected)
     */
    public function __construct(
        mixed     $result = null,
        Throwable $throwable = null,
    )
    {
        if ($throwable instanceof Throwable) {
            $this->isResolved = false;
            $this->isRejected = true;
            $this->throwable = $throwable;
        } else {
            $this->isResolved = true;
            $this->isRejected = false;
            $this->result     = $result;
        }
    }

    #[Override]
    public function then(callable $onResolved) : AsyncOperationPromise
    {
        if ($this->isResolved) {
            try {
                $newResult = $onResolved($this->result);

                return new self(result: $newResult);
            } catch (Throwable $e) {
                return new self(rejection: $e);
            }
        }

        // If rejected, pass through without calling onResolved
        return $this;
    }

    #[Override]
    public function catch(callable $onRejected) : AsyncOperationPromise
    {
        if ($this->isRejected && $this->throwable instanceof Throwable) {
            try {
                $newResult = $onRejected($this->throwable);

                return new self(result: $newResult);
            } catch (Throwable $e) {
                return new self(rejection: $e);
            }
        }

        // If resolved, pass through without calling onRejected
        return $this;
    }

    #[Override]
    public function isResolved() : bool
    {
        return $this->isResolved;
    }

    #[Override]
    public function isRejected() : bool
    {
        return $this->isRejected;
    }

    #[Override]
    public function getResult() : mixed
    {
        if ($this->isRejected && $this->throwable instanceof Throwable) {
            throw $this->throwable;
        }

        return $this->result;
    }
}
