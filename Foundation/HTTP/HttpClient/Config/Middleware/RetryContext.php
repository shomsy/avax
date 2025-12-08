<?php

declare(strict_types=1);

namespace Avax\HTTP\HttpClient\Config\Middleware;

use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * A context for managing retry logic within API client services.
 *
 * This class encapsulates the state needed to determine whether an API call should be retried,
 * including the number of retries already attempted, the original request object,
 * and optionally the response or the exception received.
 *
 * The `readonly` modifier ensures immutability after instantiation, which is
 * essential in retry logic to avoid side effects across retries.
 */
final readonly class RetryContext
{
    /**
     * @param int                    $retries   The number of retry attempts made so far.
     * @param RequestInterface       $request   The request object being retried.
     * @param ResponseInterface|null $response  The response from the previous attempt, if any.
     * @param Throwable|null         $throwable The exception encountered, if any, during the last retry attempt.
     */
    public function __construct(
        public int                    $retries,
        public RequestInterface       $request,
        public ResponseInterface|null $response = null,
        public Throwable|null         $throwable = null,
    ) {}

    /**
     * Determines if a retry is necessary based on the response status code.
     *
     * @return bool True if a retry should be attempted, false otherwise.
     */
    public function shouldRetry() : bool
    {
        // Retry on server errors (5xx) or network-related exceptions (timeout, DNS failure, etc.)
        if ($this->response instanceof ResponseInterface && $this->isServerError(
                statusCode: $this->response->getStatusCode(),
            )) {
            return true;
        }

        return $this->throwable instanceof Throwable && $this->isNetworkException(throwable: $this->throwable);
    }

    /**
     * Checks if the response status code indicates a server error (5xx).
     *
     * @param int $statusCode The HTTP status code to check.
     *
     * @return bool True if the status code is a server error, false otherwise.
     */
    private function isServerError(int $statusCode) : bool
    {
        return $statusCode >= 500 && $statusCode < 600;
    }

    /**
     * Determines if the exception is network-related and should trigger a retry.
     *
     * @param Throwable $throwable The exception to check.
     *
     * @return bool True if the exception is network-related, false otherwise.
     */
    private function isNetworkException(Throwable $throwable) : bool
    {
        return $throwable instanceof RequestException && $throwable->getCode() === 0;
    }

    /**
     * Provides the next delay time in milliseconds using exponential backoff.
     *
     * @param int $initialWaitTime The initial delay in milliseconds.
     *
     * @return int The calculated delay for the next retry attempt.
     */
    public function getNextDelay(int $initialWaitTime) : int
    {
        return (int) 2 ** $this->retries * $initialWaitTime;
    }
}
