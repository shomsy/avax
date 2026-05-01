<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\Foundation\Failure;

use RuntimeException;
use Throwable;

/**
 * Exception thrown when an HTTP request fails.
 *
 * Covers connection errors, DNS failures, SSL errors, and other
 * transport-level issues that prevent a request from completing.
 */
class HttpRequestFailed extends RuntimeException
{
    /**
     * @param string $message The error message
     * @param int $code The error code
     * @param string|null $url The URL that failed
     * @param string|null $method The HTTP method used
     * @param string|null $reason Human-readable reason for the failure
     * @param Throwable|null $previous Previous exception for chaining
     */
    public function __construct(
        string $message = 'HTTP request failed',
        int $code = 0,
        public readonly ?string $url = null,
        public readonly ?string $method = null,
        public readonly ?string $reason = null,
        Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }
}
