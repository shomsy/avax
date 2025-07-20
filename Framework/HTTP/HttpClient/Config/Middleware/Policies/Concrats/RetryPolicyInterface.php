<?php

declare(strict_types=1);

/**
 * This interface defines the contract for a retry policy that determines whether
 * an HTTP request should be retried based on the given response or exception.
 *
 * The interface is designed to be flexible enough to handle various retry conditions.
 *
 * @see ResponseInterface
 * @see Throwable
 */

namespace Gemini\HTTP\HttpClient\Config\Middleware\Policies\Concrats;

use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * An interface that defines a retry policy for handling HTTP responses and exceptions.
 *
 * The purpose of this interface is to determine whether a failed request should be retried
 * based on the given ResponseInterface or Throwable.
 *
 * This approach allows for various implementations of retry logic based on the specific needs
 * of the application, such as retrying on certain status codes, or specific types of exceptions.
 */
interface RetryPolicyInterface
{
    /**
     * Determines whether a request should be retried based on the response or exception received.
     *
     * The primary intent behind this method is to encapsulate the logic for retrying a request,
     * taking into account specific business rules or conditions that warrant a retry.
     *
     * @param ResponseInterface|null $response  The response object received from a request. This can be null
     *                                          if the request failed without a response, such as network errors.
     * @param Throwable|null         $exception The exception thrown during the request, if any. This can be null if
     *                                          the request completed without throwing an exception.
     *
     * @return bool True if the request should be retried, false otherwise.
     *
     * Some scenarios where retrying might be essential:
     * - Handling transient network issues.
     * - Recovering from server-side errors that are expected to be temporary.
     * - Managing specific HTTP status codes that indicate a retry could succeed (e.g., 502, 503, 504).
     *
     * This method should encapsulate all such conditions to ensure consistent retry logic across the application.
     */
    public function shouldRetry(ResponseInterface|null $response, Throwable|null $exception) : bool;
}
