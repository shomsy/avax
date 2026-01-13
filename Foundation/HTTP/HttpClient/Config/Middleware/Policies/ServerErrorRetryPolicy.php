<?php

declare(strict_types=1);

namespace Avax\HTTP\HttpClient\Config\Middleware\Policies;

use Avax\HTTP\HttpClient\Config\Middleware\Policies\Concrats\RetryPolicyInterface;
use GuzzleHttp\Exception\ConnectException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Retry policy to handle server errors (status codes 500-599).
 *
 * This class implements a retry policy that determines whether an HTTP request
 * should be retried based on the response received or an exception encountered.
 *
 * It is designed to automatically retry requests that result in server errors
 * (HTTP status codes 500-599).
 */
class ServerErrorRetryPolicy implements RetryPolicyInterface
{
    /**
     * @var array<int> HTTP status codes for which the request should be retried.
     */
    private array $retryStatusCodes = [500, 502, 503, 504, 429];

    /**
     * Determines whether a request should be retried based on the response or exception.
     *
     * **Technical Description**:
     * This method checks the HTTP response code against a predefined list of retryable codes.
     * If there is no response but an exception of type `ConnectException` is thrown, the method
     * instructs to retry the request, as this typically represents a network timeout or other
     * transient connection issue.
     *
     * **Business Description**:
     * This functionality ensures that temporary server or network issues do not interrupt a
     * user's experience with irreversible failures. It improves the resilience of requests by
     * allowing retries under specific circumstances, helping achieve reliable communication
     * with external services.
     *
     * @param ResponseInterface|null $response  The HTTP response (if available).
     * @param Throwable|null         $exception The thrown exception (if available).
     *
     * @return bool `true` if the request should be retried, `false` otherwise.
     */
    public function shouldRetry(ResponseInterface|null $response, Throwable|null $exception) : bool
    {
        // If there is an HTTP response, retry if the status code is in the list of allowed codes.
        if ($response instanceof ResponseInterface) {
            return in_array(needle: $response->getStatusCode(), haystack: $this->retryStatusCodes, strict: true);
        }

        // If no response exists but it is a ConnectException (e.g., timeout), attempt retry.
        return $exception instanceof ConnectException;
    }
}
