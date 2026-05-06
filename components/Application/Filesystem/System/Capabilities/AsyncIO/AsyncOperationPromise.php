<?php

declare(strict_types=1);

namespace Avax\Components\Application\Filesystem\System\Capabilities\AsyncIO;

use Throwable;

/**
 * Promise interface for async filesystem operations.
 *
 * This is a framework-level abstraction that concrete async runtimes
 * (ReactPHP, Amp, Swoole, Workerman) must implement. It provides a
 * common interface for chaining, error handling, and result retrieval
 * without coupling to any specific event loop or fiber implementation.
 *
 * Design notes:
 * - Inspired by ReactPHP Promise and Amp Promise concepts
 * - Kept minimal to avoid runtime-specific dependencies
 * - Concrete implementations should integrate with their respective
 *   event loops or fiber schedulers
 */
interface AsyncOperationPromise
{
    /**
     * Attach a callback to be executed when the operation resolves successfully.
     *
     * @param  callable(mixed):mixed  $onResolved  Callback receiving the resolved value
     * @return AsyncOperationPromise A new promise for chaining
     */
    public function then(callable $onResolved): AsyncOperationPromise;

    /**
     * Attach a callback to be executed when the operation fails.
     *
     * @param  callable(Throwable):mixed  $onRejected  Callback receiving the exception
     * @return AsyncOperationPromise A new promise for chaining
     */
    public function catch(callable $onRejected): AsyncOperationPromise;

    /**
     * Check if the promise has been resolved successfully.
     */
    public function isResolved(): bool;

    /**
     * Check if the promise has been rejected with an error.
     */
    public function isRejected(): bool;

    /**
     * Block and retrieve the final result of the operation.
     *
     * WARNING: This method should only be used at system boundaries
     * or in migration code. Calling this defeats the purpose of async
     * operations. Prefer using then()/catch() for proper async flow.
     *
     * @return mixed The resolved value
     *
     * @throws Throwable If the operation was rejected
     */
    public function getResult(): mixed;
}
