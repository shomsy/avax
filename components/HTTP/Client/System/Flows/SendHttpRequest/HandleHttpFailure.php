<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\Flows\SendHttpRequest;

use Avax\Components\HTTP\Client\System\Capabilities\Requests\OutboundRequest;
use Avax\Components\HTTP\Client\System\Capabilities\Requests\RequestOptions;
use Avax\Components\HTTP\Client\System\Capabilities\Responses\ClientResponse;
use Avax\Components\HTTP\Client\System\Foundation\Failure\HttpRequestFailed;
use Avax\Components\HTTP\Client\System\Foundation\Failure\HttpTimeout;
use Throwable;

/**
 * HandleHttpFailure - Handles HTTP request failures with retry logic.
 *
 * Analyzes failures to determine if they should be retried based on
 * the configured retry policy. Implements exponential backoff with
 * optional jitter for retry delays.
 */
final class HandleHttpFailure
{
    /**
     * Handle a request failure, potentially retrying based on the retry policy.
     *
     * @param OutboundRequest     $request       The original request
     * @param callable            $retryCallback Callback to execute for retry (receives OutboundRequest, returns
     *                                           ClientResponse)
     * @param Throwable          $exception     The exception that occurred
     * @param RequestOptions|null $options       Request options (may contain retry policy)
     *
     * @return ClientResponse The response from a successful retry
     *
     * @throws HttpRequestFailed if all retries are exhausted
     * @throws HttpTimeout if a timeout occurs and shouldn't be retried
     */
    public function handle(
        OutboundRequest $request,
        callable        $retryCallback,
        Throwable      $exception,
        ?RequestOptions $options = null,
    ) : ClientResponse
    {
        $retryPolicy = $options?->retryPolicy;

        if ($retryPolicy === null) {
            $this->rethrow($exception, $request);
        }

        $retryCount    = 0;
        $lastException = $exception;

        // Determine if we should retry based on the exception type
        if (! $this->shouldRetry($exception, $retryPolicy)) {
            $this->rethrow($exception, $request);
        }

        // Attempt retries
        while ( $retryPolicy->hasRemainingAttempts($retryCount + 1) ) {
            $retryCount++;
            $delay = $retryPolicy->delayForAttempt($retryCount);

            // Sleep for the retry delay
            if ($delay > 0) {
                usleep($delay * 1000);
            }

            try {
                $response = $retryCallback($request);

                // Check if the response status should trigger another retry
                if ($response->hasError() && $retryPolicy->shouldRetryStatus($response->statusCode)) {
                    $lastException = new HttpRequestFailed(
                        message: "HTTP error {$response->statusCode} on retry {$retryCount}",
                        url    : $request->url,
                        method : $request->method,
                    );
                    continue;
                }

                // Successful response
                return $response;
            } catch (Throwable $e) {
                $lastException = $e;

                // Check if we should retry this type of error
                if (! $this->shouldRetry($e, $retryPolicy)) {
                    break;
                }
            }
        }

        // All retries exhausted
        $this->rethrow($lastException, $request, $retryCount);
    }

    /**
     * Rethrow an exception with additional context.
     *
     * @param Throwable      $exception  The original exception
     * @param OutboundRequest $request    The request that failed
     * @param int             $retryCount Number of retries attempted
     *
     * @throws HttpRequestFailed
     * @throws HttpTimeout
     */
    private function rethrow(
        Throwable      $exception,
        OutboundRequest $request,
        int             $retryCount = 0,
    ) : never
    {
        $message = $exception->getMessage();

        if ($retryCount > 0) {
            $message .= " (after {$retryCount} retries)";
        }

        if ($exception instanceof HttpTimeout) {
            throw new HttpTimeout(
                message  : $message,
                timeoutMs: $exception->timeoutMs,
                url      : $request->url,
                method   : $request->method,
                previous : $exception,
            );
        }

        throw new HttpRequestFailed(
            message : $message,
            url     : $request->url,
            method  : $request->method,
            reason  : $exception->getMessage(),
            previous: $exception,
        );
    }

    /**
     * Determine if an exception should trigger a retry.
     */
    private function shouldRetry(Throwable $exception, $retryPolicy) : bool
    {
        if ($exception instanceof HttpTimeout) {
            return $retryPolicy->shouldRetryTimeout();
        }

        if ($exception instanceof HttpRequestFailed) {
            return $retryPolicy->shouldRetryConnectionError();
        }

        return false;
    }
}
