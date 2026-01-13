<?php

declare(strict_types=1);

namespace Avax\HTTP\HttpClient\Config\Contracts\Client\Async;

/**
 * Interface AsyncOperationInterface
 *
 * Represents an asynchronous operation that can be fulfilled or rejected.
 * Provides a standardized approach to attach success and failure callbacks.
 */
interface AsyncOperationInterface
{
    /**
     * Resolves the asynchronous operation and returns the result.
     *
     * @return mixed The result of the asynchronous operation.
     */
    public function resolve(): mixed;

    /**
     * Rejects the asynchronous operation and returns the reason for the rejection.
     *
     * @return mixed The reason for rejection.
     */
    public function reject(): mixed;

    /**
     * Attaches a callback to be executed when the operation is fulfilled.
     *
     * @param  callable  $onFulfilled  The callback to execute on success.
     * @return self The instance for chaining.
     */
    public function then(callable $onFulfilled): self;

    /**
     * Attaches a callback to be executed when the operation is rejected.
     *
     * @param  callable  $onRejected  The callback to execute on failure.
     * @return self The instance for chaining.
     */
    public function catch(callable $onRejected): self;
}
