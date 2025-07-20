<?php

declare(strict_types=1);

namespace Gemini\HTTP\HttpClient\Config\Clients\Guzzle;

use Exception;
use Gemini\HTTP\HttpClient\Config\Contracts\Client\Async\AsyncOperationInterface;
use GuzzleHttp\Promise\PromiseInterface;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * Class GuzzleAsyncOperation
 *
 * Provides standardized async operation handling using Guzzle promises.
 */
readonly class GuzzleAsyncOperation implements AsyncOperationInterface
{
    public function __construct(
        private PromiseInterface $promise,
        private LoggerInterface  $logger,
    ) {}

    /**
     * Resolves the promise and returns the result.
     *
     * @return mixed The result of the promise.
     * @throws \Throwable
     * @throws \Throwable
     */
    public function resolve() : mixed
    {
        try {
            return $this->promise->wait();
        } catch (Throwable $throwable) {
            $this->logger->error(
                message: "Async operation resolve failed",
                context: ["message" => $throwable->getMessage()],
            );
            throw $throwable;
        }
    }

    /**
     * Rejects the promise and logs the rejection.
     *
     * @return mixed The rejection reason.
     * @throws \Exception
     */
    public function reject() : mixed
    {
        try {
            return $this->promise->wait(unwrap: false);
        } catch (Throwable $throwable) {
            $this->logger->warning(
                message: "Async operation rejected",
                context: ["message" => $throwable->getMessage()],
            );
            throw new Exception(message: $throwable->getMessage(), code: $throwable->getCode(), previous: $throwable);
        }
    }

    /**
     * Attaches a success callback to the promise.
     *
     * @param callable $onFulfilled The callback for a successful response.
     *
     * @return self The current instance for chaining.
     */
    public function then(callable $onFulfilled) : self
    {
        $this->promise->then(onFulfilled: $onFulfilled);

        return $this;
    }

    /**
     * Attaches a failure callback to the promise.
     *
     * @param callable $onRejected The callback for a failed response.
     *
     * @return self The current instance for chaining.
     */
    public function catch(callable $onRejected) : self
    {
        $this->promise->otherwise(onRejected: $onRejected);

        return $this;
    }
}
