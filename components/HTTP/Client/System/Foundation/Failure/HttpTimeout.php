<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\Foundation\Failure;

use Throwable;

/**
 * Exception thrown when an HTTP request times out.
 *
 * Distinguishes timeout failures from other request failures
 * so callers can handle them differently (e.g., with longer timeouts).
 */
class HttpTimeout extends HttpRequestFailed
{
    /**
     * @param string $message The error message
     * @param float $timeoutMs The timeout that was exceeded (in milliseconds)
     * @param string|null $url The URL that timed out
     * @param string|null $method The HTTP method used
     * @param Throwable|null $previous Previous exception for chaining
     */
    public function __construct(
        string $message = 'HTTP request timed out',
        public readonly float $timeoutMs = 0.0,
        string $url = null,
        string $method = null,
        Throwable $previous = null,
    ) {
        parent::__construct(
            message : $message,
            url     : $url,
            method  : $method,
            reason  : 'timeout',
            previous: $previous,
        );
    }
}
