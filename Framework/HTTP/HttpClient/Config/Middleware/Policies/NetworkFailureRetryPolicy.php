<?php

declare(strict_types=1);

/**
 * Class NetworkFailureRetryPolicy
 *
 * This class implements the RetryPolicyInterface to provide a retry policy based on network failures.
 * It retries a request if a network failure (indicated by specific conditions) occurs.
 *
 * This policy is designed to handle transient network issues by attempting the request again,
 * under conditions where it is likely that the failure can be resolved by a simple retry.
 */

namespace Gemini\HTTP\HttpClient\Config\Middleware\Policies;

use Gemini\HTTP\HttpClient\Config\Middleware\Policies\Concrats\RetryPolicyInterface;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * Class NetworkFailureRetryPolicy
 *
 * This class implements a retry policy specifically for network failures.
 * It determines whether a request should be retried based on the response or exception encountered.
 *
 * Implements RetryPolicyInterface to ensure standard retry behavior across different policies.
 */
class NetworkFailureRetryPolicy implements RetryPolicyInterface
{
    /**
     * Determines if a request should be retried based on the given response and exception.
     *
     * @param ResponseInterface|null $response  The HTTP response from the previous request attempt.
     * @param Throwable|null         $exception The exception thrown during the previous request attempt.
     *
     * @return bool Returns true if the request should be retried; otherwise, false.
     *
     * This method specifically checks if the exception is of type RequestException with a code of 0,
     * which may denote a network error that warrants a retry. This logic is based on the assumption that
     * such exceptions are transient and retrying the request could succeed.
     */
    public function shouldRetry(ResponseInterface|null $response, Throwable|null $exception) : bool
    {
        return $exception instanceof RequestException && $exception->getCode() === 0;
    }
}
