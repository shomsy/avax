<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\Flows\SendHttpRequest;

use Avax\Components\HTTP\Client\System\Capabilities\Requests\OutboundRequest;
use Avax\Components\HTTP\Client\System\Capabilities\Requests\RequestOptions;
use Avax\Components\HTTP\Client\System\Capabilities\Resilience\RetryPolicy;
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
     * @param  OutboundRequest  $outboundRequest  The original request
     * @param  callable  $retryCallback  Callback to execute for retry (receives OutboundRequest, returns
     *                                   ClientResponse)
     * @param  Throwable  $throwable  The exception that occurred
     * @param  RequestOptions|null  $requestOptions  Request options (may contain retry policy)
     * @return ClientResponse The response from a successful retry
     *
     * @throws HttpRequestFailed if all retries are exhausted
     * @throws HttpTimeout if a timeout occurs and shouldn't be retried
     */
    public function handle(
        OutboundRequest $outboundRequest,
        callable $retryCallback,
        Throwable $throwable,
        ?RequestOptions $requestOptions = null,
    ): ClientResponse {
        $retryPolicy = $requestOptions?->retryPolicy;

        if (! $retryPolicy instanceof RetryPolicy) {
            $this->rethrow($throwable, $outboundRequest);
        }

        $retryCount = 0;
        $lastException = $throwable;

        // Determine if we should retry based on the exception type
        if (! $this->shouldRetry($throwable, $retryPolicy)) {
            $this->rethrow($throwable, $outboundRequest);
        }

        // Attempt retries
        while ($retryPolicy->hasRemainingAttempts($retryCount + 1)) {
            $retryCount++;
            $delay = $retryPolicy->delayForAttempt($retryCount);

            // Sleep for the retry delay
            if ($delay > 0) {
                usleep($delay * 1000);
            }

            try {
                $response = $retryCallback($outboundRequest);

                // Check if the response status should trigger another retry
                if ($response->hasError() && $retryPolicy->shouldRetryStatus($response->statusCode)) {
                    $lastException = new HttpRequestFailed(
                        message: sprintf('HTTP error %s on retry %d', $response->statusCode, $retryCount),
                        url    : $outboundRequest->url,
                        method : $outboundRequest->method,
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
        $this->rethrow($lastException, $outboundRequest, $retryCount);
    }

    /**
     * Rethrow an exception with additional context.
     *
     * @param  Throwable  $throwable  The original exception
     * @param  OutboundRequest  $outboundRequest  The request that failed
     * @param  int  $retryCount  Number of retries attempted
     *
     * @throws HttpRequestFailed
     * @throws HttpTimeout
     */
    private function rethrow(
        Throwable $throwable,
        OutboundRequest $outboundRequest,
        int $retryCount = 0,
    ): never {
        $message = $throwable->getMessage();

        if ($retryCount > 0) {
            $message .= sprintf(' (after %d retries)', $retryCount);
        }

        if ($throwable instanceof HttpTimeout) {
            throw new HttpTimeout(
                message  : $message,
                timeoutMs: $throwable->timeoutMs,
                url      : $outboundRequest->url,
                method   : $outboundRequest->method,
                previous : $throwable,
            );
        }

        throw new HttpRequestFailed(
            message : $message,
            url     : $outboundRequest->url,
            method  : $outboundRequest->method,
            reason  : $throwable->getMessage(),
            previous: $throwable,
        );
    }

    /**
     * Determine if an exception should trigger a retry.
     */
    private function shouldRetry(Throwable $throwable, RetryPolicy $retryPolicy): bool
    {
        if ($throwable instanceof HttpTimeout) {
            return $retryPolicy->shouldRetryTimeout();
        }

        if ($throwable instanceof HttpRequestFailed) {
            return $retryPolicy->shouldRetryConnectionError();
        }

        return false;
    }
}
