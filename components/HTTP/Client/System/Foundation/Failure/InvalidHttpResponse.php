<?php

declare(strict_types=1);

namespace Avax\Components\HTTP\Client\System\Foundation\Failure;

use RuntimeException;
use Throwable;

/**
 * Exception thrown when an HTTP response is invalid or unexpected.
 *
 * Covers cases like malformed responses, unexpected status codes,
 * or responses that cannot be decoded as expected.
 */
class InvalidHttpResponse extends RuntimeException
{
    /**
     * @param string         $message    The error message
     * @param int            $code       The error code
     * @param int|null       $statusCode The HTTP status code received (if any)
     * @param string|null    $url        The URL that returned the invalid response
     * @param mixed|null     $body       The response body (if available)
     * @param Throwable|null $previous   Previous exception for chaining
     */
    public function __construct(
        string                $message = 'Invalid HTTP response',
        int                   $code = 0,
        public readonly ?int  $statusCode = null,
        public readonly ?string $url = null,
        public readonly mixed $body = null,
        Throwable             $previous = null,
    )
    {
        parent::__construct($message, $code, $previous);
    }
}
